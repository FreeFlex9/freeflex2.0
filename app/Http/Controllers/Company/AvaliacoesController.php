<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvaliacoesController extends Controller
{
    public function index()
    {
        $company = Auth::guard('company')->user();

        // Schedules de demandas desta empresa (qualquer data)
        $schedules = Schedule::with([
            'demand:id,title,date,service_id,company_id',
            'demand.service:id,name',
            'provider:id,name,profile_photo_path',
        ])
            ->whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
            ->where('status', 'scheduled')
            ->orderByDesc('date')
            ->get();

        // Busca avaliações já feitas por esta empresa
        $ratingsFeitas = Rating::where('company_id', $company->id)
            ->where('rated_by', 'company')
            ->get()
            ->keyBy(fn ($r) => $r->demand_id . '_' . $r->provider_id);

        $items = $schedules->map(fn ($s) => [
            'schedule_id'   => $s->id,
            'demand_id'     => $s->demand_id,
            'demand_title'  => $s->demand->title ?? '',
            'demand_date'   => $s->date->format('Y-m-d'),
            'service_name'  => $s->demand->service->name ?? '',
            'provider_id'   => $s->provider_id,
            'provider_name' => $s->provider->name ?? '',
            'provider_photo'=> $s->provider->profile_photo_path,
            'rating'        => $ratingsFeitas->get($s->demand_id . '_' . $s->provider_id),
        ]);

        return inertia('Empresa/Avaliacoes', [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $company = Auth::guard('company')->user();

        $data = $request->validate([
            'demand_id'   => 'required|exists:demands,id',
            'provider_id' => 'required|exists:providers,id',
            'score'       => 'required|integer|min:1|max:5',
            'comment'     => 'nullable|string|max:1000',
        ]);

        // Verifica que a demanda pertence a esta empresa
        abort_if(
            \App\Models\Demand::where('id', $data['demand_id'])->value('company_id') !== $company->id,
            403
        );

        // Verifica que o prestador trabalhou nesta demanda
        $hasSchedule = Schedule::where('demand_id', $data['demand_id'])
            ->where('provider_id', $data['provider_id'])
            ->exists();
        abort_if(!$hasSchedule, 403, 'Prestador não foi confirmado nesta demanda.');

        Rating::updateOrCreate(
            ['demand_id' => $data['demand_id'], 'provider_id' => $data['provider_id'], 'rated_by' => 'company'],
            ['company_id' => $company->id, 'score' => $data['score'], 'comment' => $data['comment'] ?? null]
        );

        return back()->with('success', 'Avaliação enviada!');
    }
}
