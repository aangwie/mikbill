<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class MobileCustomerController extends Controller
{
    /**
     * GET /api/mobile/customers
     * List customers (paginated, searchable).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Customer::query();

        if ($user->role === 'operator') {
            $query->where('operator_id', $user->id);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('internet_number', 'like', "%{$search}%")
                    ->orWhere('pppoe_username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = $request->input('per_page', 20);
        $customers = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * GET /api/mobile/customers/{id}
     * Customer detail with invoices.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $customer = Customer::with([
            'invoices' => function ($q) {
                $q->orderBy('due_date', 'desc')->limit(12);
            }
        ])->findOrFail($id);

        // Permission check
        if ($user->role === 'operator' && $customer->operator_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    /**
     * POST /api/mobile/customers
     * Create a new customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'internet_number' => 'required|string|max:100',
            'pppoe_username' => 'nullable|string|max:100',
            'pppoe_password' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'monthly_price' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $data = $request->only([
            'name',
            'internet_number',
            'pppoe_username',
            'pppoe_password',
            'address',
            'phone',
            'monthly_price',
            'is_active',
        ]);

        if ($user->role === 'operator') {
            $data['operator_id'] = $user->id;
            $data['admin_id'] = $user->parent_id;
        } else {
            $data['admin_id'] = $user->id;
        }

        $customer = Customer::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'data' => $customer,
        ], 201);
    }

    /**
     * PUT /api/mobile/customers/{id}
     * Update customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $user = $request->user();

        // Permission check
        if ($user->role === 'operator' && $customer->operator_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'internet_number' => 'sometimes|string|max:100',
            'pppoe_username' => 'nullable|string|max:100',
            'pppoe_password' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'monthly_price' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $customer->update($request->only([
            'name',
            'internet_number',
            'pppoe_username',
            'pppoe_password',
            'address',
            'phone',
            'monthly_price',
            'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui.',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * DELETE /api/mobile/customers/{id}
     * Delete customer.
     */
    public function destroy(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $user = $request->user();

        // Permission check
        if ($user->role === 'operator' && $customer->operator_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}
