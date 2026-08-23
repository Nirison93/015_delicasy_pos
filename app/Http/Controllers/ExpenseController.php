<?php

namespace App\Http\Controllers;

use App\Models\CashDrawer;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::with('user')->latest()->get();
        return response()->json($expenses);
    }

    /**
     * Get expenses for current cash drawer
     */
    public function getCurrentDrawerExpenses()
    {
        $cashDrawer = \App\Models\CashDrawer::where('status', 'open')
            ->where('opened_by', auth()->id())
            ->latest()
            ->first();

        if (!$cashDrawer) {
            return response()->json(['expenses' => []]);
        }

        $expenses = $cashDrawer->expenses()->with('user')->get();
        return response()->json(['expenses' => $expenses]);
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
        if (!Gate::allows('hasRole', ['Admin', 'Manager', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'nullable|string|max:100',
            'payment_method' => 'required|string|in:Cash,Card,QR,Bank Transfer,Other',
            'cash_drawer_id' => 'nullable|exists:cash_drawers,id',
        ]);

        $cashDrawerId = $validated['cash_drawer_id']
            ?? CashDrawer::where('status', 'open')->latest()->value('id');

        $expense = Expense::create([
            'reason' => $validated['reason'],
            'category' => $validated['category'] ?? null,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'cash_drawer_id' => $cashDrawerId,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role_type ?? 'unknown',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense recorded successfully',
            'expense' => $expense,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        //
    }
}
