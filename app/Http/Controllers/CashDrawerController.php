<?php

namespace App\Http\Controllers;

use App\Models\CashDrawer;
use App\Models\User;
use App\Services\CashDrawerReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CashDrawerController extends Controller
{
    /**
     * Display a listing of cash drawers.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = CashDrawer::query();

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($startDate && $endDate) {
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('opened_at', [$from, $to]);
        }

        // Search by user name
        if ($search) {
            $query->whereHas('openedByUser', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('closedByUser', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $cashDrawers = $query->with(['openedByUser', 'closedByUser'])
            ->orderBy('opened_at', 'desc')
            ->paginate($perPage);

        return Inertia::render('CashDrawer/Index', [
            'cashDrawers' => $cashDrawers,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Show the form for opening a new cash drawer.
     */
    public function create()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        // Check if there's already an open drawer
        $openDrawer = CashDrawer::where('status', 'open')->first();
        if ($openDrawer) {
            return redirect()->route('cashDrawer.edit', $openDrawer)->with('message', 'A cash drawer is already open.');
        }

        return Inertia::render('CashDrawer/Create');
    }

    /**
     * Store a newly opened cash drawer.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if there's already an open drawer
        $openDrawer = CashDrawer::where('status', 'open')->first();
        if ($openDrawer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'A cash drawer is already open. Please close it before opening a new one.'
                ], 409);
            }
            return back()->withErrors(['error' => 'A cash drawer is already open. Please close it before opening a new one.']);
        }

        $cashDrawer = CashDrawer::create([
            'opened_by' => auth()->id(),
            'opening_balance' => $validated['opening_balance'],
            'opened_at' => Carbon::now(),
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'cashDrawer' => $cashDrawer,
            ], 201);
        }

        return redirect()->route('cashDrawer.index')->with('success', 'Cash drawer opened successfully.');
    }

    /**
     * Display the specified cash drawer.
     */
    public function show(CashDrawer $cashDrawer)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $cashDrawer->load(['openedByUser', 'closedByUser']);

        return Inertia::render('CashDrawer/Show', [
            'cashDrawer' => $cashDrawer,
        ]);
    }

    /**
     * Show the form for closing a cash drawer.
     */
    public function edit(CashDrawer $cashDrawer)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        if ($cashDrawer->status === 'closed') {
            return redirect()->route('cashDrawer.show', $cashDrawer)->with('message', 'This cash drawer is already closed.');
        }

        return Inertia::render('CashDrawer/Edit', [
            'cashDrawer' => $cashDrawer->load(['openedByUser']),
        ]);
    }

    /**
     * Close a cash drawer with full server-side reconciliation.
     * Only closing_balance (actual counted cash), an optional denomination
     * breakdown, and optional closing notes are ever trusted from the
     * client — every money total is recomputed here from real records.
     */
    public function update(Request $request, CashDrawer $cashDrawer, CashDrawerReconciliationService $service)
    {
        if ($cashDrawer->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This cash drawer is already closed.'
                ], 409);
            }
            return back()->withErrors(['error' => 'This cash drawer is already closed.']);
        }

        if (!$cashDrawer->canBeManagedBy(auth()->user())) {
            abort(403, 'You can only close your own cash drawer.');
        }

        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'denomination_breakdown' => 'nullable|array',
            'closing_notes' => 'nullable|string|max:1000',
        ]);

        $snapshot = $service->computeSnapshot($cashDrawer);
        $expectedCash = $snapshot['expected_cash'];
        $actualCash = (float) $validated['closing_balance'];

        $variance = round($actualCash - $expectedCash, 2);
        $varianceStatus = abs($variance) < 0.01 ? 'balanced' : ($variance > 0 ? 'over' : 'short');
        $threshold = (float) config('pos.cash_drawer.variance_threshold');
        $requiresApproval = abs($variance) > $threshold;

        if ($requiresApproval && empty($validated['closing_notes'])) {
            return response()->json([
                'message' => 'Closing notes are required when the cash difference exceeds Rs. ' . number_format($threshold, 2) . '.',
                'errors' => [
                    'closing_notes' => ['Closing notes are required when the cash difference exceeds Rs. ' . number_format($threshold, 2) . '.'],
                ],
                'expected_cash' => $expectedCash,
                'variance' => $variance,
                'variance_status' => $varianceStatus,
            ], 422);
        }

        // sales_count is a helper breakdown for the JSON preview only — not a real column.
        $persistableSnapshot = collect($snapshot)->except('sales_count')->all();

        DB::transaction(function () use ($cashDrawer, $persistableSnapshot, $validated, $actualCash, $variance, $varianceStatus, $requiresApproval) {
            $cashDrawer->update(array_merge($persistableSnapshot, [
                'closed_by' => auth()->id(),
                'closing_balance' => $actualCash,
                'closed_at' => Carbon::now(),
                'status' => 'closed',
                'denomination_breakdown' => $validated['denomination_breakdown'] ?? null,
                'closing_notes' => $validated['closing_notes'] ?? null,
                'variance' => $variance,
                'variance_status' => $varianceStatus,
                'requires_approval' => $requiresApproval,
            ]));
        });

        $cashDrawer->refresh()->load(['openedByUser', 'closedByUser', 'expenses', 'refunds', 'cashMovements']);

        if ($request->expectsJson()) {
            return response()->json([
                'cashDrawer' => $cashDrawer,
            ]);
        }

        return redirect()->route('cashDrawer.show', $cashDrawer)->with('success', 'Cash drawer closed successfully.');
    }

    /**
     * Live, unpersisted reconciliation preview for the closing modal —
     * lets the cashier see Expected Cash / payment breakdown before
     * they submit the actual close.
     */
    public function preview(CashDrawer $cashDrawer, CashDrawerReconciliationService $service)
    {
        if (!$cashDrawer->canBeManagedBy(auth()->user())) {
            abort(403, 'Unauthorized');
        }

        $snapshot = $service->computeSnapshot($cashDrawer);

        return response()->json(array_merge($snapshot, [
            'cash_drawer' => $cashDrawer->load('openedByUser'),
            'variance_threshold' => (float) config('pos.cash_drawer.variance_threshold'),
            'movements' => $cashDrawer->cashMovements()->with('user')->latest()->get(),
            'expenses' => $cashDrawer->expenses()->with('user')->latest()->get(),
        ]));
    }

    /**
     * Manager/Admin approval of a drawer whose variance exceeded the
     * threshold. Non-blocking: the drawer is already closed by the time
     * this runs, this just records the after-the-fact sign-off.
     */
    public function approve(Request $request, CashDrawer $cashDrawer)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        if (!$cashDrawer->requires_approval || $cashDrawer->approved_at !== null) {
            return response()->json([
                'message' => 'This drawer has nothing pending approval.',
            ], 409);
        }

        $cashDrawer->update([
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'cashDrawer' => $cashDrawer->fresh()->load(['approvedByUser']),
        ]);
    }

    /**
     * Get the current open cash drawer (API endpoint).
     */
    public function getOpen()
    {
        $openDrawer = CashDrawer::where('status', 'open')->with(['openedByUser'])->first();

        return response()->json($openDrawer);
    }

    /**
     * Get cash drawer activity report.
     */
    public function activityReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userId = $request->input('user_id');

        $query = CashDrawer::query();

        if ($startDate && $endDate) {
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('opened_at', [$from, $to]);
        }

        if ($userId) {
            $query->where('opened_by', $userId)->orWhere('closed_by', $userId);
        }

        $cashDrawers = $query->with(['openedByUser', 'closedByUser'])
            ->orderBy('opened_at', 'desc')
            ->get();

        // Calculate statistics
        $totalOpened = $cashDrawers->count();
        $totalClosed = $cashDrawers->where('status', 'closed')->count();
        $totalCash = $cashDrawers->where('status', 'closed')->sum('closing_balance');
        
        // Group by user
        $userActivity = [];
        foreach ($cashDrawers as $drawer) {
            $userId = $drawer->opened_by;
            if (!isset($userActivity[$userId])) {
                $userActivity[$userId] = [
                    'user' => $drawer->openedByUser,
                    'count' => 0,
                    'total_opened' => 0,
                    'total_closed' => 0,
                ];
            }
            $userActivity[$userId]['count']++;
            $userActivity[$userId]['total_opened'] += $drawer->opening_balance;
            if ($drawer->status === 'closed') {
                $userActivity[$userId]['total_closed'] += $drawer->closing_balance;
            }
        }

        return response()->json([
            'cashDrawers' => $cashDrawers,
            'statistics' => [
                'total_opened' => $totalOpened,
                'total_closed' => $totalClosed,
                'total_cash' => $totalCash,
            ],
            'userActivity' => array_values($userActivity),
        ]);
    }
}
