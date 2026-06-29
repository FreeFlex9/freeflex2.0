<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;

class BuscaPrestadoresController extends Controller
{
    public function index(Request $request)
    {
        $query = Provider::query()
            ->where('status', 'approved')
            ->where('active', true)
            ->with(['services', 'ratings'])
            ->withAvg('ratings', 'score');

        if ($request->filled('service_id')) {
            $query->whereHas('services', fn($q) => $q->where('services.id', $request->service_id));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->boolean('has_license')) {
            $query->where('has_license', true);
        }

        $providers = $query->orderByDesc('ratings_avg_score')
            ->paginate(12)
            ->withQueryString();

        return inertia('Empresa/BuscarPrestadores', [
            'providers' => $providers,
            'services'  => Service::orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['service_id', 'city', 'state', 'has_license']),
        ]);
    }
}
