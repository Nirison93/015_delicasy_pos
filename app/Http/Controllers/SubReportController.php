<?php

namespace App\Http\Controllers;

use App\Models\CompanyInfo;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Services\CashDrawerReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SubReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  Shared: parse date range from request
    // ─────────────────────────────────────────────────────────────
    private function dateRange(Request $request): array
    {
        $startRaw = $request->input('start_date');
        $endRaw   = $request->input('end_date');
        return [
            $startRaw ? Carbon::parse($startRaw)->startOfDay() : null,
            $endRaw   ? Carbon::parse($endRaw)->endOfDay()     : null,
            $startRaw,
            $endRaw,
        ];
    }

    private function applyWindow($query, $from, $to): void
    {
        if ($from && $to)   { $query->whereBetween('created_at', [$from, $to]); }
        elseif ($from)      { $query->where('created_at', '>=', $from); }
        elseif ($to)        { $query->where('created_at', '<=', $to); }
    }

    // ─────────────────────────────────────────────────────────────
    //  1. Category Wise Sales
    // ─────────────────────────────────────────────────────────────
    public function categoryWiseSales(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) abort(403);

        $perPage = $request->input('per_page', 25);
        [$from, $to, $startRaw, $endRaw] = $this->dateRange($request);

        $itemQuery = SaleItem::with(['product.category', 'sale'])
            ->whereHas('sale', function ($q) use ($from, $to) {
                $this->applyWindow($q, $from, $to);
            });

        $items = $itemQuery->get();

        // Build per-category rows
        $categories = [];
        foreach ($items as $item) {
            $catName = optional(optional($item->product)->category)->name ?? 'Uncategorised';
            $catId   = optional(optional($item->product)->category)->id ?? 0;

            if (!isset($categories[$catName])) {
                $categories[$catName] = [
                    'category'   => $catName,
                    'category_id'=> $catId,
                    'qty'        => 0,
                    'total'      => 0,
                    'transactions'=> 0,
                    'order_ids'  => [],
                ];
            }
            $categories[$catName]['qty']   += (int)   ($item->quantity ?? 0);
            $categories[$catName]['total'] += (float) ($item->total_price ?? 0);
            if (!in_array($item->sale_id, $categories[$catName]['order_ids'])) {
                $categories[$catName]['order_ids'][] = $item->sale_id;
            }
        }
        foreach ($categories as &$row) {
            $row['transactions'] = count($row['order_ids']);
            unset($row['order_ids']);
        }
        unset($row);

        $rows = collect($categories)->sortByDesc('total')->values()->toArray();

        // Paginate the aggregated results
        $paginated = array_slice($rows, ($request->input('page', 1) - 1) * $perPage, $perPage);

        // Create pagination object
        $items = new \Illuminate\Pagination\Paginator(
            $paginated,
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $items->total = count($rows);

        return Inertia::render('Reports/CategoryWiseSales', [
            'rows'        => $items,
            'startDate'   => $startRaw,
            'endDate'     => $endRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  2. Order Type Report
    // ─────────────────────────────────────────────────────────────
    public function orderTypeReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) abort(403);

        $perPage = $request->input('per_page', 25);
        [$from, $to, $startRaw, $endRaw] = $this->dateRange($request);

        $salesQuery = Sale::query();
        $this->applyWindow($salesQuery, $from, $to);
        $sales = $salesQuery->get();

        $types = [];
        foreach ($sales as $sale) {
            $type = $sale->order_type ?: 'Unknown';
            if (!isset($types[$type])) {
                $types[$type] = [
                    'order_type'   => $type,
                    'transactions' => 0,
                    'total'        => 0,
                    'discount'     => 0,
                    'profit'       => 0,
                ];
            }
            $types[$type]['transactions']++;
            $types[$type]['total']    += (float) ($sale->total_amount ?? 0);
            $types[$type]['discount'] += (float) ($sale->discount ?? 0);
            $types[$type]['profit']   += (float) ($sale->total_amount ?? 0) - (float) ($sale->total_cost ?? 0);
        }

        $rows = collect($types)->sortByDesc('total')->values()->toArray();

        // Paginate the aggregated results
        $paginated = array_slice($rows, ($request->input('page', 1) - 1) * $perPage, $perPage);

        // Create pagination object
        $items = new \Illuminate\Pagination\Paginator(
            $paginated,
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $items->total = count($rows);

        return Inertia::render('Reports/OrderTypeReport', [
            'rows'        => $items,
            'startDate'   => $startRaw,
            'endDate'     => $endRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  3. Bar Sales Report  (categories whose category_type = 'bar'
    //     OR whose name contains 'bar', case-insensitive)
    // ─────────────────────────────────────────────────────────────
    public function barSalesReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) abort(403);

        $perPage = $request->input('per_page', 25);
        [$from, $to, $startRaw, $endRaw] = $this->dateRange($request);

        // Find bar categories (category_type = 1 means Bar throughout this system)
        $barCategoryIds = Category::where('category_type', 1)
            ->pluck('id')
            ->toArray();

        $itemQuery = SaleItem::with(['product.category', 'sale'])
            ->whereHas('product', fn($q) => $q->whereIn('category_id', $barCategoryIds))
            ->whereHas('sale', function ($q) use ($from, $to) {
                $this->applyWindow($q, $from, $to);
            });

        $items = $itemQuery->get();

        // Per-product rows
        $products = [];
        foreach ($items as $item) {
            $prodName = optional($item->product)->name ?? 'N/A';
            $catName  = optional(optional($item->product)->category)->name ?? 'N/A';
            if (!isset($products[$prodName])) {
                $products[$prodName] = [
                    'product'  => $prodName,
                    'category' => $catName,
                    'qty'      => 0,
                    'total'    => 0,
                ];
            }
            $products[$prodName]['qty']   += (int)   ($item->quantity ?? 0);
            $products[$prodName]['total'] += (float) ($item->total_price ?? 0);
        }

        $rows = collect($products)->sortByDesc('total')->values()->toArray();

        // Paginate the aggregated results
        $paginated = array_slice($rows, ($request->input('page', 1) - 1) * $perPage, $perPage);

        // Create pagination object
        $items = new \Illuminate\Pagination\Paginator(
            $paginated,
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $items->total = count($rows);

        // Also send bar category names for context
        $barCategories = Category::whereIn('id', $barCategoryIds)->pluck('name')->toArray();

        return Inertia::render('Reports/BarSalesReport', [
            'rows'          => $items,
            'barCategories' => $barCategories,
            'startDate'     => $startRaw,
            'endDate'       => $endRaw,
            'companyInfo'   => CompanyInfo::first(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  4. Payment Method Report
    // ─────────────────────────────────────────────────────────────
    public function paymentMethodReport(Request $request, CashDrawerReconciliationService $service)
    {
        if (!Gate::allows('hasRole', ['Admin'])) abort(403);

        [$from, $to, $startRaw, $endRaw] = $this->dateRange($request);

        $salesQuery = Sale::query();
        $this->applyWindow($salesQuery, $from, $to);
        $sales = $salesQuery->select('payment_method', 'total_amount')->get();

        $labels = [
            'cash' => 'Cash',
            'card' => 'Card',
            'qr' => 'QR / Online',
            'bank_transfer' => 'Bank Transfer',
            'other' => 'Other',
        ];

        $buckets = [];
        foreach ($labels as $key => $label) {
            $buckets[$key] = ['method' => $label, 'transactions' => 0, 'total' => 0.0];
        }

        foreach ($sales as $sale) {
            $bucket = $service->bucketPaymentMethod($sale->payment_method);
            $buckets[$bucket]['transactions']++;
            $buckets[$bucket]['total'] += (float) $sale->total_amount;
        }

        $grandTotal = array_sum(array_column($buckets, 'total'));
        foreach ($buckets as &$row) {
            $row['total'] = round($row['total'], 2);
            $row['percent'] = $grandTotal > 0 ? round(($row['total'] / $grandTotal) * 100, 1) : 0;
        }
        unset($row);

        return Inertia::render('Reports/PaymentMethod', [
            'buckets'     => array_values($buckets),
            'grandTotal'  => round($grandTotal, 2),
            'startDate'   => $startRaw,
            'endDate'     => $endRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  5. Expense Report
    // ─────────────────────────────────────────────────────────────
    public function expenseReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) abort(403);

        $perPage = $request->input('per_page', 25);
        [$from, $to, $startRaw, $endRaw] = $this->dateRange($request);

        $userId = $request->input('user_id');
        $category = $request->input('category');
        $paymentMethod = $request->input('payment_method');

        $applyFilters = function ($query) use ($from, $to, $userId, $category, $paymentMethod) {
            $this->applyWindow($query, $from, $to);
            if ($userId) {
                $query->where('user_id', $userId);
            }
            if ($category) {
                $query->where('category', $category);
            }
            if ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            }
        };

        $expenseQuery = Expense::with(['user', 'cashDrawer']);
        $applyFilters($expenseQuery);
        $expensesPaginated = $expenseQuery->orderBy('created_at', 'desc')->paginate($perPage);

        $allExpensesQuery = Expense::query();
        $applyFilters($allExpensesQuery);
        $allExpenses = $allExpensesQuery->get();

        $totalsByPaymentMethod = $allExpenses->groupBy('payment_method')->map(
            fn($g) => round((float) $g->sum('amount'), 2)
        )->toArray();

        $categories = Expense::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return Inertia::render('Reports/Expense', [
            'expenses'              => $expensesPaginated,
            'totalExpenses'         => round((float) $allExpenses->sum('amount'), 2),
            'totalCount'            => $allExpenses->count(),
            'totalsByPaymentMethod' => $totalsByPaymentMethod,
            'categories'            => $categories,
            'cashiers'              => User::whereIn('role_type', ['Admin', 'Manager', 'Cashier'])->select('id', 'name')->orderBy('name')->get(),
            'filters' => [
                'user_id' => $userId,
                'category' => $category,
                'payment_method' => $paymentMethod,
            ],
            'startDate'   => $startRaw,
            'endDate'     => $endRaw,
            'companyInfo' => CompanyInfo::first(),
        ]);
    }
}
