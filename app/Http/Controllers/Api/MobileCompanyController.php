<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class MobileCompanyController extends Controller
{
    /**
     * GET /api/mobile/company
     * Get company info for current tenant.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $adminId = $user->role === 'operator' ? $user->parent_id : $user->id;

        $company = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('admin_id', $adminId)
            ->first();

        // Fallback to superadmin's company
        if (!$company) {
            $company = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->whereHas('admin', fn($q) => $q->where('role', 'superadmin'))
                ->first();
        }

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Data perusahaan belum diatur.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company_name' => $company->company_name,
                'address' => $company->address,
                'phone' => $company->phone,
                'email' => $company->email,
                'logo_path' => $company->logo_path,
            ],
        ]);
    }
}
