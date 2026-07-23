<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user();

        $mes = $request->integer('mes', now()->month);
        $ano = $request->integer('ano', now()->year);

        $query = Schedule::with(['demand.company:id,trade_name', 'demand.service:id,name'])
            ->where('provider_id', $provider->id);

        if (!$request->boolean('todos')) {
            $query->whereMonth('date', $mes)->whereYear('date', $ano);
        }

        $schedules = $query->orderBy('date')->orderBy('start_time')->get()
            ->map(fn (Schedule $s) => [
                'id' => $s->id,
                'data' => $s->date?->toDateString(),
                'hora_inicio' => substr($s->start_time, 0, 5),
                'hora_fim' => substr($s->end_time, 0, 5),
                'status' => $s->status,
                'descricao' => $s->description,
                'empresa' => $s->demand?->company?->trade_name,
                'servico' => $s->demand?->service?->name,
                'demanda_id' => $s->demand_id,
                'cidade' => $s->demand?->city,
                'estado' => $s->demand?->state,
            ]);

        return response()->json(['agendamentos' => $schedules, 'mes' => $mes, 'ano' => $ano]);
    }
}
