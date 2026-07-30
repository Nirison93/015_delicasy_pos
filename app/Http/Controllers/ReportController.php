<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\CompanyInfo;
use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\CashDrawer;
use App\Models\SaleItem;
use App\Models\Expense;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */





  public function index(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDir = $request->input('sort_dir', 'desc');

    $startDateRaw = $request->input('start_date');
    $endDateRaw   = $request->input('end_date');

    $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
    $to   = $endDateRaw   ? Carbon::parse($endDateRaw)->endOfDay()     : null;

    $applyCreatedWindow = function ($q) use ($from, $to) {
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $q->where('created_at', '>=', $from);
        } elseif ($to) {
            $q->where('created_at', '<=', $to);
        }
    };

    // -------- Top Products (paginated) --------
    $productsQuery = Product::query();
    if ($from || $to) {
        $productsQuery->whereHas('saleItems.sale', function ($q) use ($applyCreatedWindow) {
            $applyCreatedWindow($q);
        });
    }
    $productsPaginated = $productsQuery->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'products_page');

    // For qty per product
    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyCreatedWindow, $from, $to) {
        if ($from || $to) $applyCreatedWindow($q);
    });

    $salesQuantities = $salesQuantitiesQuery
        ->select('product_id')
        ->selectRaw('SUM(quantity) as total_sales_qty')
        ->groupBy('product_id')
        ->get()
        ->keyBy('product_id');

    $productsPaginated->transform(function ($product) use ($salesQuantities) {
        $product->sales_qty = (float) ($salesQuantities->get($product->id)->total_sales_qty ?? 0);
        return $product;
    });

    // -------- Sales (paginated) --------
    $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);

    if ($from || $to) {
        $applyCreatedWindow($salesQuery);
    }

    if (in_array($sortBy, ['total_amount', 'sale_date'])) {
        $salesQuery->orderBy($sortBy, $sortDir);
    } else {
        $salesQuery->orderBy('created_at', $sortDir);
    }

    $salesPaginated = $salesQuery->paginate($perPage, ['*'], 'sales_page');

    // Helper for discount calculation
    $customDiscountToLkr = function ($sale) {
        $gross = (float) ($sale->total_amount ?? 0);
        $val   = (float) ($sale->custom_discount ?? 0);
        $type  = $sale->custom_discount_type ?? 'fixed';
        return $type === 'percent' ? ($gross * $val / 100.0) : $val;
    };

    // -------- Calculate totals from ALL filtered data (not just paginated) --------
    $allSalesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);
    if ($from || $to) {
        $applyCreatedWindow($allSalesQuery);
    }
    $allSales = $allSalesQuery->get();

    // Category totals (from all filtered sales)
    $categorySales = [];
    foreach ($allSales as $sale) {
        foreach ($sale->saleItems as $item) {
            $categoryName = $item->product->category->name ?? 'No Category';
            $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + (float) $item->total_price;
        }
    }

    // Payment totals
    $paymentMethodTotals = $allSales->groupBy('payment_method')->map(
        fn($g) => (float) $g->sum('total_amount')
    )->toArray();

    // Employee sales
    $employeeSalesSummary = [];
    foreach ($allSales as $sale) {
        if (!$sale->employee) continue;
        $name = $sale->employee->name;
        $employeeSalesSummary[$name] ??= [
            'Employee Name' => $name,
            'Total Sales Amount' => 0,
        ];
        $gross       = (float) ($sale->total_amount ?? 0);
        $prodDisc    = (float) ($sale->discount ?? 0);
        $customDisc  = $customDiscountToLkr($sale);
        $employeeSalesSummary[$name]['Total Sales Amount'] += ($gross - $prodDisc - $customDisc);
    }

    // Overall stats (from all filtered data)
    $totalSaleAmount         = (float) $allSales->sum('total_amount');
    $totalCost               = (float) $allSales->sum('total_cost');
    $totalProductDiscountLkr = (float) $allSales->sum('discount');
    $totalCustomDiscountLkr  = (float) $allSales->reduce(fn($c, $s) => $c + $customDiscountToLkr($s), 0.0);
    $netProfit               = $totalSaleAmount - $totalCost - ($totalProductDiscountLkr + $totalCustomDiscountLkr);
    $totalTransactions       = $allSales->count();
    $averageTransactionValue = $totalTransactions > 0 ? ($totalSaleAmount / $totalTransactions) : 0;

    // Distinct customers
    $totalCustomer = $allSales->groupBy('customer_id')->count();

    return Inertia::render('Reports/Index', [
        'products'                  => $productsPaginated,
        'sales'                     => $salesPaginated,

        'totalSaleAmount'           => round($totalSaleAmount, 2),
        'totalDiscountLkr'          => round($totalProductDiscountLkr, 2),
        'totalCustomDiscountLkr'    => round($totalCustomDiscountLkr, 2),
        'netProfit'                 => round($netProfit, 2),
        'totalTransactions'         => $totalTransactions,
        'averageTransactionValue'   => round($averageTransactionValue, 2),
        'totalCustomer'             => $totalCustomer,

        'startDate'                 => $startDateRaw,
        'endDate'                   => $endDateRaw,

        'categorySales'             => $categorySales,
        'employeeSalesSummary'      => $employeeSalesSummary,
        'paymentMethodTotals'       => $paymentMethodTotals,
        'companyInfo'               => CompanyInfo::first(),
    ]);
}

    /**
     * Cash Drawer Report
     */
    public function cashDrawerReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');
        $userId = $request->input('user_id');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to = $endDateRaw ? Carbon::parse($endDateRaw)->endOfDay() : null;

        // Query for pagination (drawers only)
        $drawerQuery = CashDrawer::with(['openedByUser', 'closedByUser', 'expenses']);

        if ($from && $to) {
            $drawerQuery->whereBetween('opened_at', [$from, $to]);
        } elseif ($from) {
            $drawerQuery->where('opened_at', '>=', $from);
        } elseif ($to) {
            $drawerQuery->where('opened_at', '<=', $to);
        }

        if ($userId) {
            $drawerQuery->where(function ($q) use ($userId) {
                $q->where('opened_by', $userId)->orWhere('closed_by', $userId);
            });
        }

        $cashDrawersPaginated = $drawerQuery->orderBy('opened_at', 'desc')->paginate($perPage);

        // Query all drawers (unfiltered by pagination) for statistics
        $allDrawersQuery = CashDrawer::with(['openedByUser', 'closedByUser', 'expenses']);
        if ($from && $to) {
            $allDrawersQuery->whereBetween('opened_at', [$from, $to]);
        } elseif ($from) {
            $allDrawersQuery->where('opened_at', '>=', $from);
        } elseif ($to) {
            $allDrawersQuery->where('opened_at', '<=', $to);
        }
        if ($userId) {
            $allDrawersQuery->where(function ($q) use ($userId) {
                $q->where('opened_by', $userId)->orWhere('closed_by', $userId);
            });
        }
        $allCashDrawers = $allDrawersQuery->get();

        // Query expenses directly (some expenses may not be linked to a drawer)
        $expenseQuery = Expense::with('user');
        if ($from && $to) {
            $expenseQuery->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $expenseQuery->where('created_at', '>=', $from);
        } elseif ($to) {
            $expenseQuery->where('created_at', '<=', $to);
        }
        if ($userId) {
            $expenseQuery->where('user_id', $userId);
        }
        $expensesPaginated = $expenseQuery->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'expenses_page');

        // Calculate statistics from all filtered data
        $totalDrawers = $allCashDrawers->count();
        $openDrawers = $allCashDrawers->where('status', 'open')->count();
        $closedDrawers = $allCashDrawers->where('status', 'closed')->count();

        $totalOpeningBalance = (float) $allCashDrawers->sum('opening_balance');
        $totalClosingBalance = (float) $allCashDrawers->where('status', 'closed')->sum('closing_balance');

        // Calculate total expenses
        $totalExpenses = (float) $expensesPaginated->getCollection()->sum('amount');

        // Variance calculation (including expenses)
        $totalVariance = 0;
        $varianceByUser = [];

        foreach ($allCashDrawers as $drawer) {
            if ($drawer->status === 'closed') {
                $expenses = $drawer->expenses->sum('amount');
                $variance = $drawer->closing_balance - $drawer->opening_balance - $expenses;
                $totalVariance += $variance;

                $drawerId = $drawer->opened_by;
                if (!isset($varianceByUser[$drawerId])) {
                    $varianceByUser[$drawerId] = [
                        'user_id' => $drawerId,
                        'user_name' => $drawer->openedByUser->name ?? 'Unknown',
                        'count' => 0,
                        'total_variance' => 0,
                        'total_opened' => 0,
                        'total_closed' => 0,
                        'total_expenses' => 0,
                    ];
                }
                $varianceByUser[$drawerId]['count']++;
                $varianceByUser[$drawerId]['total_variance'] += $variance;
                $varianceByUser[$drawerId]['total_opened'] += $drawer->opening_balance;
                $varianceByUser[$drawerId]['total_closed'] += $drawer->closing_balance;
                $varianceByUser[$drawerId]['total_expenses'] += $expenses;
            }
        }

        return Inertia::render('Reports/CashDrawer', [
            'cashDrawers' => $cashDrawersPaginated,
            'expenses' => $expensesPaginated,
            'statistics' => [
                'total_drawers' => $totalDrawers,
                'open_drawers' => $openDrawers,
                'closed_drawers' => $closedDrawers,
                'total_opening_balance' => round($totalOpeningBalance, 2),
                'total_closing_balance' => round($totalClosingBalance, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_variance' => round($totalVariance, 2),
            ],
            'varianceByUser' => array_values($varianceByUser),
            'startDate' => $startDateRaw,
            'endDate' => $endDateRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }

    /**
     * User Activity Report (Cash Drawer + Sales)
     */
    public function userActivityReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to = $endDateRaw ? Carbon::parse($endDateRaw)->endOfDay() : null;

        // Get cash drawer activity
        $cashDrawerQuery = CashDrawer::with(['openedByUser', 'closedByUser']);
        if ($from && $to) {
            $cashDrawerQuery->whereBetween('opened_at', [$from, $to]);
        } elseif ($from) {
            $cashDrawerQuery->where('opened_at', '>=', $from);
        } elseif ($to) {
            $cashDrawerQuery->where('opened_at', '<=', $to);
        }

        $cashDrawers = $cashDrawerQuery->get();

        // Get sales activity
        $salesQuery = Sale::with(['employee', 'customer']);
        if ($from || $to) {
            if ($from && $to) {
                $salesQuery->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $salesQuery->where('created_at', '>=', $from);
            } elseif ($to) {
                $salesQuery->where('created_at', '<=', $to);
            }
        }

        $sales = $salesQuery->get();

        // Aggregate user activity
        $userActivity = [];

        // Process cash drawers
        foreach ($cashDrawers as $drawer) {
            $userId = $drawer->opened_by;
            if (!isset($userActivity[$userId])) {
                $userActivity[$userId] = [
                    'user_id' => $userId,
                    'user_name' => $drawer->openedByUser->name ?? 'Unknown',
                    'cash_drawers_opened' => 0,
                    'cash_drawers_closed' => 0,
                    'sales_count' => 0,
                    'sales_amount' => 0,
                    'opening_balance' => 0,
                    'closing_balance' => 0,
                ];
            }
            $userActivity[$userId]['cash_drawers_opened']++;
            $userActivity[$userId]['opening_balance'] += $drawer->opening_balance;

            if ($drawer->status === 'closed') {
                $userActivity[$userId]['cash_drawers_closed']++;
                $userActivity[$userId]['closing_balance'] += $drawer->closing_balance;
            }
        }

        // Process sales
        foreach ($sales as $sale) {
            if ($sale->employee_id) {
                $userId = $sale->employee_id;
                // Since sales are linked to employee_id not user_id, we need to handle this differently
                // For now, we'll use employee name
                $key = 'emp_' . $userId;
                if (!isset($userActivity[$key])) {
                    $userActivity[$key] = [
                        'user_id' => $userId,
                        'user_name' => $sale->employee->name ?? 'Unknown Employee',
                        'cash_drawers_opened' => 0,
                        'cash_drawers_closed' => 0,
                        'sales_count' => 0,
                        'sales_amount' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => 0,
                    ];
                }
                $userActivity[$key]['sales_count']++;
                $userActivity[$key]['sales_amount'] += $sale->total_amount;
            }
        }

        return Inertia::render('Reports/UserActivity', [
            'userActivity' => array_values($userActivity),
            'cashDrawers' => $cashDrawers,
            'startDate' => $startDateRaw,
            'endDate' => $endDateRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }

















    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
