<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileCompany;
use Illuminate\Http\Request;

class MobileCompanyController extends Controller
{
    /**
     * GET /api/mobile/company
     * Get company info for current tenant (Mobile-specific scoping).
     */
    public function index(Request $request)
    {
        // MobileTenantScope handles isolation automatically for admin_id
        $company = MobileCompany::first();

        // Fallback to superadmin's company if current tenant has none
        if (!$company) {
            $company = MobileCompany::withoutGlobalScopes()
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
