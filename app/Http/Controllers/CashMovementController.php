<?php

namespace App\Http\Controllers;

use App\Models\CashDrawer;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CashMovementController extends Controller
{
    /**
     * Record a manual Cash In / Cash Out / Cash Drop against the currently open drawer.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $cashDrawer = CashDrawer::where('status', 'open')->latest()->first();
        if (!$cashDrawer) {
            return response()->json([
                'message' => 'No open cash drawer found.',
            ], 423);
        }

        $validated = $request->validate([
            'type' => 'required|in:cash_in,cash_out,cash_drop',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required_unless:type,cash_in|nullable|string|max:255',
        ]);

        $movement = CashMovement::create([
            'cash_drawer_id' => $cashDrawer->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'movement' => $movement->load('user'),
        ], 201);
    }

    /**
     * List cash movements for the currently open drawer.
     */
    public function currentDrawer()
    {
        $cashDrawer = CashDrawer::where('status', 'open')->latest()->first();

        if (!$cashDrawer) {
            return response()->json(['movements' => []]);
        }

        $movements = $cashDrawer->cashMovements()->with('user')->latest()->get();

        return response()->json(['movements' => $movements]);
    }
}
