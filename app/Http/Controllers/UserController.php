<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\WhatsappSetting;
use App\Models\RouterSetting;
use App\Models\MailSetting;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Company;

class UserController extends Controller
{
    // READ: Tampilkan List User
    public function index()
    {
        $user = Auth::user();
        $query = User::with(['plan', 'parent'])->orderBy('created_at', 'desc');

        if ($user->isAdmin()) {
            // Admin bisa lihat dirinya sendiri dan operator miliknya
            $query->where(function ($q) use ($user) {
                $q->where('id', $user->id)
                    ->orWhere('parent_id', $user->id);
            });
        } elseif ($user->isOperator()) {
            // Operator harusnya tidak bisa akses ini (middleware), tapi jika lewat, filter
            $query->where('id', $user->id);
        }

        $users = $query->get();

        // Ambil daftar Admin untuk dropdown jika Superadmin mau buat operator
        $admins = User::where('role', User::ROLE_ADMIN)->get();

        return view('users.index', compact('users', 'admins'));
    }

    // CREATE: Simpan User Baru
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:superadmin,admin,operator',
            'parent_id' => 'nullable|exists:users,id',
            'is_activated' => 'nullable|boolean'
        ]);

        $role = $request->role;
        $parentId = $request->parent_id;

        // Force rules for Admin
        if ($currentUser->isAdmin()) {
            $role = User::ROLE_OPERATOR;
            $parentId = $currentUser->id;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'parent_id' => $parentId ?? ($currentUser->isSuperAdmin() ? $request->parent_id : $currentUser->id),
            'is_activated' => $request->has('is_activated'),
            'email_verified_at' => $request->has('is_verified') ? now() : null,
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan.');
    }

    // UPDATE: Simpan Perubahan User
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Security Check: Admin hanya bisa edit dirinya sendiri atau operator miliknya
        if ($currentUser->isAdmin()) {
            $isSelf = ($user->id == $currentUser->id);
            $isOperator = ($user->parent_id == $currentUser->id && $user->role == User::ROLE_OPERATOR);

            if (!$isSelf && !$isOperator) {
                abort(403, 'Akses Ditolak.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:superadmin,admin,operator',
            'password' => 'nullable|min:6',
            'is_activated' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Role management & Activation for Superadmin
        if ($currentUser->isSuperAdmin()) {
            $data['role'] = $request->role;
            $data['is_activated'] = $request->has('is_activated');

            // Manual Email Verification
            if ($request->has('is_verified')) {
                if (!$user->email_verified_at) {
                    $data['email_verified_at'] = now();
                }
            } else {
                $data['email_verified_at'] = null;
            }

            if ($request->filled('parent_id')) {
                $data['parent_id'] = $request->parent_id;
            }
        }

        // Activation for Admin (only for their operators)
        if ($currentUser->isAdmin() && $user->role == User::ROLE_OPERATOR && $user->parent_id == $currentUser->id) {
            $data['is_activated'] = $request->has('is_activated');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data user diperbarui.');
    }

    // DELETE: Hapus User
    public function destroy($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Security Check
        if ($currentUser->isAdmin() && ($user->parent_id != $currentUser->id || $user->role != User::ROLE_OPERATOR)) {
            abort(403, 'Akses Ditolak.');
        }

        if ($currentUser->id == $id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        try {
            DB::beginTransaction();

            // Cascading Delete if it's an Admin
            if ($user->role === 'admin') {
                $adminId = $user->id;

                // 1. Delete all Operators under this Admin
                User::where('parent_id', $adminId)->where('role', 'operator')->delete();

                // 2. Delete all Customers, Invoices, Expenses
                Customer::where('admin_id', $adminId)->delete();
                Invoice::where('admin_id', $adminId)->delete();
                Expense::where('admin_id', $adminId)->delete();

                // 3. Delete Settings
                WhatsappSetting::where('admin_id', $adminId)->delete();
                RouterSetting::where('admin_id', $adminId)->delete();
                MailSetting::where('admin_id', $adminId)->delete();

                // 4. Delete Company Profile and Files
                $companies = Company::where('admin_id', $adminId)->get();
                foreach ($companies as $company) {
                    if ($company->logo_path) {
                        Storage::disk('hosting')->delete($company->logo_path);
                    }
                    if ($company->signature_path) {
                        Storage::disk('hosting')->delete($company->signature_path);
                    }
                    $company->delete();
                }
            }

            // Finally delete the Admin/User itself
            $user->delete();

            DB::commit();
            return back()->with('success', 'User dan seluruh data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menghapus user: ' . $e->getMessage());
        }
    }
    public function suspendSubscription(Request $request, $id)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $user = User::findOrFail($id);

        // Toggle activation
        $user->is_activated = !$user->is_activated;
        $user->save();

        $status = $user->is_activated ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Paket dan akses user {$user->name} berhasil {$status}.");
    }

    public function removeSubscription(Request $request, $id)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $user = User::findOrFail($id);

        $user->plan_id = null;
        $user->plan_expires_at = null;
        $user->is_activated = false;
        $user->save();

        return back()->with('success', "Paket user {$user->name} berhasil dihapus/direset.");
    }
}