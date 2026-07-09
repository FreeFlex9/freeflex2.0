<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PontosController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = Schedule::with([
            'provider:id,name',
            'demand:id,company_id,title,city,state',
        ])->whereHas('demand', fn ($q) => $q->where('company_id', $company->id));

        if ($request->filled('prestador')) {
            $query->whereHas('provider', fn ($q) => $q->where('name', 'like', '%' . $request->prestador . '%'));
        }
        if ($request->filled('data')) {
            $query->whereDate('date', $request->data);
        }

        $pontos = $query->orderByDesc('date')->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Empresa/Pontos/Index', [
            'pontos'  => $pontos,
            'filters' => $request->only(['prestador', 'data']),
        ]);
    }
}
