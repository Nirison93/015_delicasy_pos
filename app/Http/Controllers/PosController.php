<?php

namespace App\Http\Controllers;
use App\Models\Owner;
use App\Models\OwnerItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Size;
use App\Models\CashDrawer;
use App\Models\BankServiceCharge;
use App\Models\Delivery;
use App\Models\ServiceCharge;
use App\Models\StockTransaction;
use App\Models\Employee;
use App\Models\WaiterOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Inertia\Inertia;

class PosController extends Controller
{

     public function index(Request $request)
    {


        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $allcategories = Category::with([
        'parent',
        'products' => function ($query) {
            $query->orderBy('name', 'asc');
        },
        'products.size',
        'products.category'
    ])
    ->orderBy('name', 'asc')
    ->get()
    ->map(function ($category) {
        $category->hierarchy_string = $category->hierarchy_string;

        $category->products = $category->products
            ->where('category_id', $category->id)
            ->values();

        return $category;
    });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $serviceCharge = ServiceCharge::orderBy('created_at', 'desc')->get();
        $bankCharge    = BankServiceCharge::orderBy('created_at', 'desc')->get();
        $delivery      = Delivery::orderBy('created_at', 'desc')->get();




        $allemployee = Employee::orderBy('created_at', 'desc')->get();

        // NEW: Owners with current-month discount (or latest fallback)
      $owners = Owner::with([
        'thisMonthItem:id,owner_id,discount_value,current_discount,month',
        'latestItem:id,owner_id,discount_value,current_discount,month'
    ])
    ->orderBy('name')
    ->get()
    ->map(function ($o) {
        $item = $o->thisMonthItem ?? $o->latestItem;
        return [
            'id'               => $o->id,
            'name'             => $o->name,
            'code'             => $o->code,
            'status'           => $o->status,
            'discount_value'   => $item->discount_value ?? null,
            'current_discount' => $item->current_discount ?? null,

        ];
    });


        // Compute the next order ID from the last sale in the DB
        $lastSale = Sale::latest('id')->first();
        $nextOrderId = 'Delicasy/0001';
        if ($lastSale && $lastSale->order_id) {
            if (preg_match('/(\d+)$/', $lastSale->order_id, $matches)) {
                $nextNum = intval($matches[1]) + 1;
                $nextOrderId = 'Delicasy/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        }

        $restaurantTables = DB::table('restaurant_tables')->orderBy('number', 'asc')->get();

        return Inertia::render('Pos/Index', [
            'product'           => null,
            'error'             => null,
            'loggedInUser'      => Auth::user(),
            'allcategories'     => $allcategories,
            'allemployee'       => $allemployee,
            'colors'            => $colors,
            'sizes'             => $sizes,
            'owners'            => $owners,
            'serviceCharge'     => $serviceCharge,
            'bankCharge'        => $bankCharge,
            'delivery'          => $delivery,
            'nextOrderId'       => $nextOrderId,
            'restaurantTables'  => $restaurantTables,
        ]);
    }

    // AJAX: store a new restaurant table
    public function storeTable(Request $request)
    {
        $request->validate(['number' => 'required|integer|min:1']);

        if (DB::table('restaurant_tables')->where('number', $request->number)->exists()) {
            return response()->json(['error' => 'Table number already exists'], 422);
        }

        $id = DB::table('restaurant_tables')->insertGetId([
            'number'     => $request->number,
            'name'       => 'Table ' . $request->number,
            'status'     => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'number' => $request->number]);
    }

    // AJAX: delete a restaurant table
    public function destroyTable($id)
    {
        DB::table('restaurant_tables')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    // AJAX: Get products by IDs (for waiter order loading)
    public function getProductsByIds(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
        ]);

        $products = Product::whereIn('id', $request->product_ids)
            ->with(['category', 'colors', 'sizes'])
            ->get();

        return response()->json(['products' => $products], 200);
    }

    // AJAX: get the next sequential order ID based on the last sale in the DB
    public function getNextOrderId()
    {
        $lastSale = Sale::latest('id')->first();
        $nextOrderId = 'Delicasy/0001';
        if ($lastSale && $lastSale->order_id) {
            if (preg_match('/(\d+)$/', $lastSale->order_id, $matches)) {
                $nextNum = intval($matches[1]) + 1;
                $nextOrderId = 'Delicasy/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        }
        return response()->json(['nextOrderId' => $nextOrderId]);
    }

    // AJAX: get the last 20 orders for the POS Last Order List panel
    public function getLastOrders()
    {
        $sales = Sale::with(['saleItems.product', 'user', 'customer'])
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(function ($sale) {
                return [
                    'id'                  => $sale->id,
                    'order_id'            => $sale->order_id,
                    'total_amount'        => $sale->total_amount,
                    'discount'            => $sale->discount,
                    'custom_discount'     => $sale->custom_discount,
                    'payment_method'      => $sale->payment_method,
                    'order_type'          => $sale->order_type,
                    'sale_date'           => $sale->sale_date
                        ?? $sale->created_at?->format('Y-m-d H:i'),
                    'cashier'             => $sale->user?->name ?? '—',
                    'customer'            => $sale->customer?->name ?? null,
                    'delivery_charge'     => $sale->delivery_charge,
                    'service_charge'      => $sale->service_charge,
                    'bank_service_charge' => $sale->bank_service_charge,
                    'items'               => $sale->saleItems->map(fn($i) => [
                        'name'       => $i->product?->name ?? 'Unknown',
                        'qty'        => $i->quantity,
                        'unit_price' => $i->unit_price,
                        'total'      => $i->total_price,
                    ]),
                ];
            });

        return response()->json(['orders' => $sales]);
    }

    // AJAX: fetch current discount for selected owner (prefer this month; else latest)
    public function getOwnerDiscount(Request $request)
    {
        $request->validate([
            'owner_id' => ['required','exists:owners,id'],
        ]);

        $owner = Owner::with(['thisMonthItem', 'latestItem'])->findOrFail($request->owner_id);

        $item = $owner->thisMonthItem ?? $owner->latestItem;

        if (!$item) {
            return response()->json([
                'owner_id'         => $owner->id,
                'discount_value'   => 0,
                'current_discount' => 0,
                'month'            => now()->startOfMonth()->format('Y-m'),
                'available'        => false,
                'message'          => 'No discount found for this owner.',
            ]);
        }

        return response()->json([
            'owner_id'         => $owner->id,
            'discount_value'   => (float) $item->discount_value, // fixed LKR
            'current_discount'   => (float) $item->current_discount, // fixed LKR

            'month'            => $item->month->format('Y-m'),
            'available'        => true,
        ]);
    }

    public function getProduct(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required',
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('code', $request->barcode)
            ->first();

        return response()->json([
            'product' => $product,
            'error' => $product ? null : 'Product not found',
        ]);
    }

    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }

    public function submit(Request $request)
    {



        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }
        $openDrawer = CashDrawer::where('status', 'open')->first();
        if (!$openDrawer) {
            return response()->json([
                'message' => 'Opening balance required. Please open the cash drawer first.'
            ], 423);
        }
        // Combine countryCode and contactNumber to create the phone field


        $customer = null;

        $products = $request->input('products');
        $totalAmount = collect($products)->reduce(function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['selling_price']);
        }, 0);

        $totalCost = collect($products)->reduce(function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['cost_price']);
        }, 0);

        $productDiscounts = collect($products)->reduce(function ($carry, $product) {
            if (isset($product['discount']) && $product['discount'] > 0 && isset($product['apply_discount']) && $product['apply_discount'] != false) {
                $discountAmount = ($product['selling_price'] - $product['discounted_price']) * $product['quantity'];
                return $carry + $discountAmount;
            }
            return $carry;
        }, 0);

        // Get coupon discount if applied
        $couponDiscount = isset($request->input('appliedCoupon')['discount']) ?
            floatval($request->input('appliedCoupon')['discount']) : 0;


    $ownerId   = $request->input('owner_id');
    $ownerDisc = (float) $request->input('owner_discount_value', 0);

        // Calculate total combined discount
        $totalDiscount = $productDiscounts + $couponDiscount ;

        // Normalize order_type to match DB ENUM ['dine_in', 'takeaway', 'delivery']
        $rawOrderType = $request->input('order_type');
        $orderTypeMap = ['takeaway' => 'takeaway', 'pickup' => 'delivery', 'dine_in' => 'dine_in'];
        $orderType = $orderTypeMap[$rawOrderType] ?? null;

        // Helpers: convert empty strings to null for nullable columns
        $toDecimal = fn($v) => is_numeric($v) ? (float) $v : null;
        $toStr     = fn($v) => ($v !== null && $v !== '') ? $v : null;

        DB::beginTransaction(); // Start a transaction

        try {
            // Generate the next sequential order ID inside the transaction with a lock
            // so concurrent submissions never produce the same number.
            $lastSale = Sale::lockForUpdate()->latest('id')->first();
            $generatedOrderId = 'Delicasy/0001';
            if ($lastSale && $lastSale->order_id) {
                if (preg_match('/(\d+)$/', $lastSale->order_id, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                    $generatedOrderId = 'Delicasy/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                }
            }

            // Save the customer data to the database
            if ($request->input('customer.contactNumber') || $request->input('customer.name') || $request->input('customer.email')) {

                $phone = $request->input('customer.countryCode') . $request->input('customer.contactNumber');
                $customer = Customer::where('email', $request->input('customer.email'))->first();
                $customer1 = Customer::where('phone', $phone)->first();

                if ($customer) {
                    $email = '';
                } else {
                    $email = $request->input('customer.email');
                }

                if ($customer1) {
                    $phone = '';
                }

                if (!empty($phone) || !empty($email) || !empty($request->input('customer.name'))) {
                    $customer = Customer::create([
                        'name' => $request->input('customer.name'),
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $request->input('customer.address', ''), // Optional address
                        'member_since' => now()->toDateString(), // Current date
                        'loyalty_points' => 0, // Default value
                    ]);
                }
            }

            // Create the sale record
            $sale = Sale::create([
                'customer_id'         => $customer ? $customer->id : null,
                'employee_id'         => $request->input('employee_id'),
                'user_id'             => $request->input('userId'),
                'order_id'            => $generatedOrderId,
                'total_amount'        => $request->input('total', $totalAmount),
                'discount'            => $totalDiscount,
                'total_cost'          => $totalCost,
                'payment_method'      => $request->input('paymentMethod'),
                'sale_date'           => now()->toDateString(),
                'cash'                => $request->input('cash'),
                'custom_discount'     => $request->input('custom_discount'),
                'order_type'          => $orderType,
                'kitchen_note'        => $toStr($request->input('kitchen_note')),
                'delivery_charge'     => $toDecimal($request->input('delivery_charge')),
                'service_charge'      => $toDecimal($request->input('service_charge')),
                'bank_service_charge' => $toDecimal($request->input('bank_service_charge')) ?? 0,
                'shopping_bag_charge' => $toDecimal($request->input('shopping_bag_charge')) ?? 0,
                'bank_name'           => $toStr($request->input('bank_name')),
                'card_last4'          => $toStr($request->input('card_last4')),
                'owner_id'            => $request->input('owner_id') ?: null,
                'owner_discount_value' => $request->input('owner_discount_value') ?: 0,
            ]);

            foreach ($products as $product) {
                // Check stock before saving sale items
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    $newStockQuantity = $productModel->stock_quantity - $product['quantity'];

                    // Prevent stock from going negative
                    if ($newStockQuantity < 0) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for product: {$productModel->name}
                            ({$productModel->stock_quantity} available)",
                        ], 423);
                    }

                    if ($productModel->expire_date && now()->greaterThan($productModel->expire_date)) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "The product '{$productModel->name}' has expired (Expiration Date: {$productModel->expire_date->format('Y-m-d')}).",
                        ], 423); // HTTP 422 Unprocessable Entity
                    }

                    // Create sale item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['selling_price'],
                        'total_price' => $product['quantity'] * $product['selling_price'],
                    ]);

                    StockTransaction::create([
                        'product_id' => $product['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $product['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $productModel->supplier_id ?? null,
                    ]);

                    // Update stock quantity
                    $productModel->update([
                        'stock_quantity' => $newStockQuantity,
                    ]);
                }
            }

 if ($ownerId && $ownerDisc > 0) {
            // Try to get the row and lock it; if not found, create it initialized to 0 then increment.
            $ownerItem = OwnerItem::where('owner_id', $ownerId)->lockForUpdate()->first();

            if ($ownerItem) {
                // use Eloquent's increment for atomicity
                $ownerItem->increment('current_discount', $ownerDisc);
            } else {
                OwnerItem::create([
                    'owner_id'         => $ownerId,
                    'discount_value'   => 0,               // keep as-is or set from your business rule
                    'current_discount' => $ownerDisc,      // first increment
                    'status'           => 'active',               // or whatever default you use
                ]);
            }
        }


            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Sale recorded successfully!', 'orderId' => $generatedOrderId], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Customer details saved successfully!',
            'data' => $customer,
        ], 201);
    }

    /**
     * Get pending waiter orders for POS alert
     */
    public function getWaiterOrders(Request $request)
    {
        $request->validate([
            'table_id' => 'nullable|integer',
        ]);

        $query = WaiterOrder::where('status', 'pending')
            ->with(['waiter'])
            ->orderBy('created_at', 'desc');

        if ($request->table_id) {
            $query->where('table_id', $request->table_id);
        }

        $orders = $query->get();

        // Manually attach table data from restaurant_tables
        $orders->each(function ($order) {
            $table = DB::table('restaurant_tables')->find($order->table_id);
            $order->table = $table;
        });

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Confirm waiter order in POS
     */
    public function confirmWaiterOrder(Request $request, $orderId)
    {
        $request->validate([
            'action' => 'required|in:confirm,reject',
            'reason' => 'nullable|string',
        ]);

        $order = WaiterOrder::where('order_id', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($request->action === 'confirm') {
            $order->update(['status' => 'confirmed']);
        } elseif ($request->action === 'reject') {
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order ' . $request->action . 'ed successfully',
            'order' => $order,
        ], 200);
    }
}
