<?php

namespace App\Http\Controllers;

use App\Models\CashDrawer;
use App\Models\Refund;
use App\Models\Sale;
use App\Services\CashDrawerReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RefundController extends Controller
{
    /**
     * Issue a refund against an existing sale. Additive to the existing
     * hard-delete "void sale" flow (TransactionHistoryController) — this
     * does not touch stock, SaleItems, or the Sale record itself, it only
     * records a money ledger entry so reports/cash-drawer reconciliation
     * can account for it.
     */
    public function store(Request $request, CashDrawerReconciliationService $service)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'sale_id' => 'required|integer|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $sale = Sale::findOrFail($validated['sale_id']);

        $alreadyRefunded = (float) Refund::where('sale_id', $sale->id)->sum('amount');
        $remaining = (float) $sale->total_amount - $alreadyRefunded;

        if ($validated['amount'] > $remaining + 0.001) {
            return response()->json([
                'message' => 'Refund amount exceeds the remaining refundable balance of Rs. ' . number_format($remaining, 2) . '.',
            ], 422);
        }

        $bucket = $service->bucketPaymentMethod($sale->payment_method);
        $cashDrawerId = null;
        if ($bucket === 'cash') {
            $cashDrawerId = CashDrawer::where('status', 'open')->latest()->value('id');
        }

        $refund = Refund::create([
            'sale_id' => $sale->id,
            'order_id' => $sale->order_id,
            'cash_drawer_id' => $cashDrawerId,
            'amount' => $validated['amount'],
            'payment_method' => $sale->payment_method,
            'reason' => $validated['reason'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund recorded successfully.',
            'refund' => $refund,
        ], 201);
    }
}
