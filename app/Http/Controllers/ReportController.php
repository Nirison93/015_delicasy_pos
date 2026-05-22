<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\CompanyInfo;
use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\CashDrawer;
use App\Models\SaleItem;

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

    // Dates (normalize to day bounds)
    $startDateRaw = $request->input('start_date');
    $endDateRaw   = $request->input('end_date');

    $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
    $to   = $endDateRaw   ? Carbon::parse($endDateRaw)->endOfDay()     : null;

    // Reusable created_at window
    $applyCreatedWindow = function ($q) use ($from, $to) {
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $q->where('created_at', '>=', $from);
        } elseif ($to) {
            $q->where('created_at', '<=', $to);
        }
    };

    // -------- Top Products (sold in range via Sale.created_at) --------
    if ($from || $to) {
        $productIds = SaleItem::whereHas('sale', function ($q) use ($applyCreatedWindow) {
                $applyCreatedWindow($q);
            })
            ->pluck('product_id')
            ->unique();

        $products = Product::whereIn('id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $products = Product::orderBy('created_at', 'desc')->get();
    }

    // -------- Sales (filter by created_at) --------
 $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);


    if ($from || $to) {
        $applyCreatedWindow($salesQuery);
    }

    // For qty per product (respect same window through parent sale)
    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyCreatedWindow, $from, $to) {
        if ($from || $to) $applyCreatedWindow($q);
    });

    $salesQuantities = $salesQuantitiesQuery
        ->select('product_id')
        ->selectRaw('SUM(quantity) as total_sales_qty')
        ->groupBy('product_id')
        ->get()
        ->keyBy('product_id');

    // Attach sales_qty to products
    $products->transform(function ($product) use ($salesQuantities) {
        $product->sales_qty = (float) ($salesQuantities->get($product->id)->total_sales_qty ?? 0);
        return $product;
    });

    $sales = $salesQuery->orderBy('created_at', 'desc')->get();
   

  


    // Helpers
    $customDiscountToLkr = function ($sale) {
        $gross = (float) ($sale->total_amount ?? 0);
        $val   = (float) ($sale->custom_discount ?? 0);
        $type  = $sale->custom_discount_type ?? 'fixed';
        return $type === 'percent' ? ($gross * $val / 100.0) : $val;
    };

    // Category totals (from filtered sales)
    $categorySales = [];
    foreach ($sales as $sale) {
        foreach ($sale->saleItems as $item) {
            $categoryName = $item->product->category->name ?? 'No Category';
            $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + (float) $item->total_price;
        }
    }

    // Payment totals (gross)
    $paymentMethodTotals = $sales->groupBy('payment_method')->map(
        fn($g) => (float) $g->sum('total_amount')
    )->toArray();

    // Employee sales (NET)
    $employeeSalesSummary = [];
    foreach ($sales as $sale) {
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

    // Overall stats
    $totalSaleAmount         = (float) $sales->sum('total_amount');
    $totalCost               = (float) $sales->sum('total_cost');
    $totalProductDiscountLkr = (float) $sales->sum('discount');
    $totalCustomDiscountLkr  = (float) $sales->reduce(fn($c, $s) => $c + $customDiscountToLkr($s), 0.0);
    $netProfit               = $totalSaleAmount - $totalCost - ($totalProductDiscountLkr + $totalCustomDiscountLkr);
    $totalTransactions       = $sales->count();
    $averageTransactionValue = $totalTransactions > 0 ? ($totalSaleAmount / $totalTransactions) : 0;

    // Distinct customers (same filter)
    $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

    

    
  

    return Inertia::render('Reports/Index', [
        'products'                  => $products,
        'sales'                     => $sales,

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

        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');
        $userId = $request->input('user_id');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to = $endDateRaw ? Carbon::parse($endDateRaw)->endOfDay() : null;

        // Query cash drawers
        $query = CashDrawer::with(['openedByUser', 'closedByUser']);

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

        $cashDrawers = $query->orderBy('opened_at', 'desc')->get();

        // Calculate statistics
        $totalDrawers = $cashDrawers->count();
        $openDrawers = $cashDrawers->where('status', 'open')->count();
        $closedDrawers = $cashDrawers->where('status', 'closed')->count();
        
        $totalOpeningBalance = (float) $cashDrawers->sum('opening_balance');
        $totalClosingBalance = (float) $cashDrawers->where('status', 'closed')->sum('closing_balance');
        
        // Variance calculation
        $totalVariance = 0;
        $varianceByUser = [];
        
        foreach ($cashDrawers as $drawer) {
            if ($drawer->status === 'closed') {
                $variance = $drawer->closing_balance - $drawer->opening_balance;
                $totalVariance += $variance;
                
                $userId = $drawer->opened_by;
                if (!isset($varianceByUser[$userId])) {
                    $varianceByUser[$userId] = [
                        'user_id' => $userId,
                        'user_name' => $drawer->openedByUser->name ?? 'Unknown',
                        'count' => 0,
                        'total_variance' => 0,
                        'total_opened' => 0,
                        'total_closed' => 0,
                    ];
                }
                $varianceByUser[$userId]['count']++;
                $varianceByUser[$userId]['total_variance'] += $variance;
                $varianceByUser[$userId]['total_opened'] += $drawer->opening_balance;
                $varianceByUser[$userId]['total_closed'] += $drawer->closing_balance;
            }
        }

        return Inertia::render('Reports/CashDrawer', [
            'cashDrawers' => $cashDrawers,
            'statistics' => [
                'total_drawers' => $totalDrawers,
                'open_drawers' => $openDrawers,
                'closed_drawers' => $closedDrawers,
                'total_opening_balance' => round($totalOpeningBalance, 2),
                'total_closing_balance' => round($totalClosingBalance, 2),
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
