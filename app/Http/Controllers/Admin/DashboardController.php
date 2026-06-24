<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Provider;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'companies_pending' => Company::where('status', 'pending')->count(),
                'providers_pending' => Provider::where('status', 'pending')->count(),
                'proposals_pending' => Proposal::where('status', 'pending_admin_approval')->count(),
                'demands_open'      => Demand::where('status', 'open')->count(),
                'companies_total'   => Company::count(),
                'providers_total'   => Provider::count(),
            ],
        ]);
    }
}
