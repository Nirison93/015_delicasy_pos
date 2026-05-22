<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allcustomers = Customer::orderBy('created_at', 'desc')
            ->get();
        $totalCustomers = $allcustomers->count();

        return Inertia::render('Customers/Index', [
            'allcustomers' => $allcustomers,
            'totalCustomers' => $allcustomers,
        ]);
    }

    public function checkCustomer(Request $request)
    {
        $request->validate([
            'contactNumber' => 'required|string',
        ]);

        // Search for customer by contact number
        $customer = Customer::where('phone', $request->contactNumber)->first();

        if ($customer) {
            return response()->json(['customer' => $customer], 200);
        }

        return response()->json(['message' => 'Customer not found'], 404);
    }

    /**
     * Save customer from POS (create or find existing)
     */
    public function saveCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        // If phone is provided, check if customer already exists
        if (!empty($validated['phone'])) {
            $existingCustomer = Customer::where('phone', $validated['phone'])->first();
            if ($existingCustomer) {
                // Update existing customer
                $existingCustomer->update($validated);
                return response()->json([
                    'customer' => $existingCustomer,
                    'message' => 'Customer updated successfully.'
                ], 200);
            }
        }

        // Create new customer
        $customer = Customer::create($validated);

        return response()->json([
            'customer' => $customer,
            'message' => 'Customer created successfully.'
        ], 201);
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers'),
            ],
            'address' => 'nullable|string|max:255',
            'bdate' => 'nullable|date',
            'loyalty_points' => 'nullable|integer',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'customer' => $customer,
            'message' => 'Customer created successfully.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'address' => 'nullable|string|max:255',
            'bdate' => 'nullable|date',
            'loyalty_points' => 'nullable|integer',
        ]);

        $customer->update($validated);

        // Return JSON for API requests, redirect for web requests
        if ($request->wantsJson()) {
            return response()->json([
                'customer' => $customer,
                'message' => 'Customer updated successfully.'
            ], 200);
        }

        return redirect()->route('customers.index')->banner('Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {

        $customer->delete();
        return redirect()->route('customers.index')->banner('Customer Deleted successfully.');
    }
}
