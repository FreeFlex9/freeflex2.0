<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PontosController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with([
            'provider:id,name',
            'demand:id,company_id,title,city,state',
            'demand.company:id,trade_name',
        ]);

        if ($request->filled('empresa')) {
            $query->whereHas('demand.company', fn ($q) => $q->where('trade_name', 'like', '%' . $request->empresa . '%'));
        }
        if ($request->filled('prestador')) {
            $query->whereHas('provider', fn ($q) => $q->where('name', 'like', '%' . $request->prestador . '%'));
        }
        if ($request->filled('data')) {
            $query->whereDate('date', $request->data);
        }

        $pontos = $query->orderByDesc('date')->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Pontos/Index', [
            'pontos'  => $pontos,
            'filters' => $request->only(['empresa', 'prestador', 'data']),
        ]);
    }
}
