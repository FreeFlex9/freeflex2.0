<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $mes = $request->integer('mes', now()->month);
        $ano = $request->integer('ano', now()->year);

        $schedules = Schedule::with([
            'demand.company:id,trade_name',
            'demand.service:id,name',
        ])
            ->where('provider_id', $provider->id)
            ->whereMonth('date', $mes)
            ->whereYear('date', $ano)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'id'           => $s->id,
                'date'         => $s->date->format('Y-m-d'),
                'start_time'   => substr($s->start_time, 0, 5),
                'end_time'     => substr($s->end_time, 0, 5),
                'status'       => $s->status,
                'description'  => $s->description,
                'company_name' => $s->demand->company->trade_name ?? '',
                'service_name' => $s->demand->service->name ?? '',
                'demand_id'    => $s->demand_id,
                'city'         => $s->demand->city ?? '',
                'state'        => $s->demand->state ?? '',
            ]);

        return inertia('Prestador/MinhaAgenda', [
            'schedules' => $schedules,
            'mes'       => $mes,
            'ano'       => $ano,
        ]);
    }
}
