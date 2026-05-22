<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Size;
use App\Models\WaiterOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WaiterController extends Controller
{
    /**
     * Display waiter dashboard with tables
     */
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Waiter'])) {
            abort(403, 'Unauthorized');
        }

        // Get all restaurant tables
        $tables = DB::table('restaurant_tables')
            ->orderBy('number', 'asc')
            ->get()
            ->map(function ($table) {
                $table->orderId = null;
                return $table;
            });

        // Get product categories
        $allcategories = Category::with('parent')
            ->get()
            ->map(function ($category) {
                $category->hierarchy_string = $category->hierarchy_string;
                return $category;
            });

        // Get product data
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();

        // Get all products grouped by category (only active items)
        $allproducts = Product::where('status', 1)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $loggedInUser = Auth::user();

        return Inertia::render('Waiter/Index', [
            'tables' => $tables,
            'allcategories' => $allcategories,
            'allproducts' => $allproducts,
            'loggedInUser' => $loggedInUser,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Get products by category
     */
    public function getProduct(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer',
        ]);

        $products = Product::where('category_id', $request->category_id)
            ->where('status', 1)
            ->with(['category', 'colors', 'sizes'])
            ->get();

        return response()->json(['products' => $products], 200);
    }

    /**
     * Submit waiter order to POS
     */
    public function submitOrder(Request $request)
    {
        $request->validate([
            'table_id' => 'required',
            'products' => 'required|array',
            'waiter_id' => 'required|integer',
        ]);

        try {
            $products = $request->products;
            $tableId = $request->table_id;
            $waiterId = $request->waiter_id;

            // Generate order ID
            $today = new \DateTime();
            $formattedDate = $today->format('y.m.d');
            $lastOrder = Sale::whereDate('created_at', today())
                ->latest('id')
                ->first();
            $nextNumber = $lastOrder ? intval(substr($lastOrder->order_id, -4)) + 1 : 1;
            $orderId = 'Delicasy/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Create waiter order record
            $waiterOrder = WaiterOrder::create([
                'table_id' => $tableId,
                'waiter_id' => $waiterId,
                'products' => json_encode($products),
                'status' => 'pending', // pending, confirmed, delivered, cancelled
                'order_id' => $orderId,
            ]);

            // Broadcast notification to POS (if using real-time features)
            // For now, we'll store it and POS will poll

            return response()->json([
                'success' => true,
                'message' => 'Order submitted successfully',
                'order' => $waiterOrder,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending orders for a specific waiter
     */
    public function getWaiterOrders(Request $request)
    {
        $waiterId = Auth::id();
        $orders = WaiterOrder::where('waiter_id', $waiterId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Cancel a waiter order
     */
    public function cancelOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $order = WaiterOrder::where('order_id', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->waiter_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
        ], 200);
    }
}
