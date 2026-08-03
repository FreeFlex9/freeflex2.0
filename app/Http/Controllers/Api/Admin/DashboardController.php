<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'nome' => Str::before($admin->email, '@'),
            ],
            'stats' => [
                'empresas_pendentes' => Company::where('status', 'pending')->count(),
                'prestadores_pendentes' => Provider::where('status', 'pending')->count(),
                'cnh_pendentes' => Provider::where('status', 'approved')->where('cnh_status', 'pending')->count(),
                'propostas_pendentes' => Proposal::where('status', 'pending_admin_approval')->count(),
                'demandas_abertas' => Demand::where('status', 'open')->count(),
            ],
        ]);
    }
}
