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
use App\Models\Refund;
use App\Models\User;

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
    $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer', 'owner', 'refunds']);

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
    $allSalesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer', 'owner', 'refunds']);
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

    // Refunds within the filtered range
    $totalRefunds = round((float) $allSales->flatMap->refunds->sum('amount'), 2);

    // Lightweight Cash Drawer summary for the same date range (Section 13 —
    // extends the Sales Report rather than duplicating a Daily Summary page)
    $drawersInRangeQuery = CashDrawer::query();
    if ($from && $to) {
        $drawersInRangeQuery->whereBetween('opened_at', [$from, $to]);
    } elseif ($from) {
        $drawersInRangeQuery->where('opened_at', '>=', $from);
    } elseif ($to) {
        $drawersInRangeQuery->where('opened_at', '<=', $to);
    }
    $drawersInRange = $drawersInRangeQuery->get();
    $cashDrawerSummary = [
        'total_drawers' => $drawersInRange->count(),
        'total_variance' => round((float) $drawersInRange->where('status', 'closed')->sum('variance'), 2),
        'pending_approval_count' => $drawersInRange->where('requires_approval', true)->whereNull('approved_at')->count(),
    ];

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
        'totalRefunds'              => $totalRefunds,
        'cashDrawerSummary'         => $cashDrawerSummary,

        'startDate'                 => $startDateRaw,
        'endDate'                   => $endDateRaw,

        'categorySales'             => $categorySales,
        'employeeSalesSummary'      => $employeeSalesSummary,
        'paymentMethodTotals'       => $paymentMethodTotals,
        'companyInfo'               => CompanyInfo::first(),
    ]);
}

    /**
     * Cash Drawer Report — reconciliation history + payment-method breakdown.
     */
    public function cashDrawerReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $perPage = $request->input('per_page', 10);
        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');
        $userId = $request->input('user_id');
        $drawerId = $request->input('drawer_id');
        $status = $request->input('status'); // open | closed | pending_approval
        $paymentMethod = $request->input('payment_method');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to = $endDateRaw ? Carbon::parse($endDateRaw)->endOfDay() : null;

        $applyFilters = function ($query) use ($from, $to, $userId, $drawerId, $status, $paymentMethod) {
            if ($from && $to) {
                $query->whereBetween('opened_at', [$from, $to]);
            } elseif ($from) {
                $query->where('opened_at', '>=', $from);
            } elseif ($to) {
                $query->where('opened_at', '<=', $to);
            }

            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('opened_by', $userId)->orWhere('closed_by', $userId);
                });
            }

            if ($drawerId) {
                $query->where('id', $drawerId);
            }

            if ($status === 'pending_approval') {
                $query->where('requires_approval', true)->whereNull('approved_at');
            } elseif (in_array($status, ['open', 'closed'])) {
                $query->where('status', $status);
            }

            if ($paymentMethod) {
                $query->whereHas('sales', function ($q) use ($paymentMethod) {
                    $q->whereRaw('LOWER(payment_method) = ?', [strtolower($paymentMethod)]);
                });
            }
        };

        // Query for pagination (drawers only)
        $drawerQuery = CashDrawer::with(['openedByUser', 'closedByUser', 'approvedByUser']);
        $applyFilters($drawerQuery);
        $cashDrawersPaginated = $drawerQuery->orderBy('opened_at', 'desc')->paginate($perPage);

        // Query all drawers (unfiltered by pagination) for statistics
        $allDrawersQuery = CashDrawer::query();
        $applyFilters($allDrawersQuery);
        $allCashDrawers = $allDrawersQuery->get();

        // Query expenses within the same date window (for the "Expenses in this
        // period" mini table — some expenses may not be linked to a drawer)
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
        $pendingApprovalCount = $allCashDrawers->where('requires_approval', true)->whereNull('approved_at')->count();

        $closedCashDrawers = $allCashDrawers->where('status', 'closed');
        $totalOpeningBalance = (float) $allCashDrawers->sum('opening_balance');
        $totalClosingBalance = (float) $closedCashDrawers->sum('closing_balance');
        $totalExpenses = (float) $closedCashDrawers->sum('total_expenses');
        $totalVariance = (float) $closedCashDrawers->sum('variance');

        // Payment-method totals across the filtered closed drawers (cheap —
        // reads the already-fetched snapshot columns, no extra query)
        $paymentMethodTotals = [
            'cash' => round((float) $closedCashDrawers->sum('cash_sales'), 2),
            'card' => round((float) $closedCashDrawers->sum('card_sales'), 2),
            'qr' => round((float) $closedCashDrawers->sum('qr_sales'), 2),
            'bank_transfer' => round((float) $closedCashDrawers->sum('bank_transfer_sales'), 2),
            'other' => round((float) $closedCashDrawers->sum('other_sales'), 2),
        ];

        $varianceByUser = [];
        foreach ($closedCashDrawers as $drawer) {
            $drawerUserId = $drawer->opened_by;
            if (!isset($varianceByUser[$drawerUserId])) {
                $varianceByUser[$drawerUserId] = [
                    'user_id' => $drawerUserId,
                    'user_name' => $drawer->openedByUser->name ?? 'Unknown',
                    'count' => 0,
                    'total_variance' => 0,
                    'total_opened' => 0,
                    'total_closed' => 0,
                    'total_expenses' => 0,
                ];
            }
            $varianceByUser[$drawerUserId]['count']++;
            $varianceByUser[$drawerUserId]['total_variance'] += (float) $drawer->variance;
            $varianceByUser[$drawerUserId]['total_opened'] += (float) $drawer->opening_balance;
            $varianceByUser[$drawerUserId]['total_closed'] += (float) $drawer->closing_balance;
            $varianceByUser[$drawerUserId]['total_expenses'] += (float) $drawer->total_expenses;
        }

        return Inertia::render('Reports/CashDrawer', [
            'cashDrawers' => $cashDrawersPaginated,
            'expenses' => $expensesPaginated,
            'statistics' => [
                'total_drawers' => $totalDrawers,
                'open_drawers' => $openDrawers,
                'closed_drawers' => $closedDrawers,
                'pending_approval_count' => $pendingApprovalCount,
                'total_opening_balance' => round($totalOpeningBalance, 2),
                'total_closing_balance' => round($totalClosingBalance, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_variance' => round($totalVariance, 2),
                'payment_method_totals' => $paymentMethodTotals,
            ],
            'varianceByUser' => array_values($varianceByUser),
            'cashiers' => User::whereIn('role_type', ['Admin', 'Manager', 'Cashier'])->select('id', 'name')->orderBy('name')->get(),
            'filters' => [
                'drawer_id' => $drawerId,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'user_id' => $userId,
            ],
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












}
