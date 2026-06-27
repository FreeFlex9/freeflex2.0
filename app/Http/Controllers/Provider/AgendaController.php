<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $modo = $request->input('modo', 'mes');
        $mes  = $request->integer('mes', now()->month);
        $ano  = $request->integer('ano', now()->year);
        $dataInicio = null;

        $query = Schedule::with([
            'demand.company:id,trade_name',
            'demand.service:id,name',
        ])->where('provider_id', $provider->id);

        if ($modo === 'semana') {
            if ($request->filled('data_inicio')) {
                $inicio = Carbon::createFromFormat('Y-m-d', $request->input('data_inicio'))->startOfDay();
            } else {
                $today = now();
                $inicio = $today->copy()->subDays($today->dayOfWeek)->startOfDay();
            }
            $fim = $inicio->copy()->addDays(6);
            $dataInicio = $inicio->format('Y-m-d');
            $query->whereBetween('date', [$dataInicio, $fim->format('Y-m-d')]);
        } elseif ($modo === 'ano') {
            $query->whereYear('date', $ano);
        } else {
            $query->whereMonth('date', $mes)->whereYear('date', $ano);
        }

        $schedules = $query->orderBy('date')->orderBy('start_time')->get()
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
            'schedules'   => $schedules,
            'mes'         => $mes,
            'ano'         => $ano,
            'modo'        => $modo,
            'data_inicio' => $dataInicio,
        ]);
    }
}
