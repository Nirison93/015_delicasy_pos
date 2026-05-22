<?php

namespace App\Http\Controllers;

use App\Models\CompanyInfo;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
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

        $rows = array_values(
            collect($categories)->sortByDesc('total')->toArray()
        );

        return Inertia::render('Reports/CategoryWiseSales', [
            'rows'        => $rows,
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

        $rows = array_values(
            collect($types)->sortByDesc('total')->toArray()
        );

        return Inertia::render('Reports/OrderTypeReport', [
            'rows'        => $rows,
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

        $rows = array_values(
            collect($products)->sortByDesc('total')->toArray()
        );

        // Also send bar category names for context
        $barCategories = Category::whereIn('id', $barCategoryIds)->pluck('name')->toArray();

        return Inertia::render('Reports/BarSalesReport', [
            'rows'          => $rows,
            'barCategories' => $barCategories,
            'startDate'     => $startRaw,
            'endDate'       => $endRaw,
            'companyInfo'   => CompanyInfo::first(),
        ]);
    }
}
