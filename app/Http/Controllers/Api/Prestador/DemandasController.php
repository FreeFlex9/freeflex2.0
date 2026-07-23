<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Service;
use App\Support\Geo;
use Illuminate\Http\Request;

class DemandasController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user();

        $query = Demand::with(['company:id,trade_name', 'service:id,name,requires_license,provider_rate'])
            ->whereIn('status', ['open', 'partially_scheduled'])
            ->whereDate('date', '>=', now()->toDateString())
            ->when(!$provider->has_license, function ($q) {
                $q->whereHas('service', fn ($s) => $s->where('requires_license', false));
            })
            ->whereNotExists(function ($sub) use ($provider) {
                $sub->from('proposals')
                    ->whereColumn('proposals.demand_id', 'demands.id')
                    ->where('proposals.provider_id', $provider->id)
                    ->whereNotIn('proposals.status', ['rejected_provider']);
            });

        if ($request->boolean('my_services')) {
            $serviceIds = $provider->services()->pluck('services.id');
            $query->whereIn('service_id', $serviceIds);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', $q)
                    ->orWhere('description', 'like', $q)
                    ->orWhere('city', 'like', $q)
                    ->orWhere('neighborhood', 'like', $q);
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $demands = $query->orderBy('date')->orderBy('start_time')->get();

        if ($provider->latitude && $provider->longitude) {
            $demands = $demands->map(function ($d) use ($provider) {
                $d->distance_km = ($d->latitude && $d->longitude)
                    ? Geo::distanceKm($provider->latitude, $provider->longitude, $d->latitude, $d->longitude)
                    : null;
                return $d;
            });

            if ($request->get('sort') === 'distance') {
                $demands = $demands->sortBy(fn ($d) => $d->distance_km ?? 9999)->values();
            }
        }

        return response()->json([
            'demandas' => $demands->map(fn (Demand $d) => $this->formatDemand($d)),
            'meus_servicos' => $provider->services()->orderBy('name')->get(['services.id', 'services.name']),
            'todos_servicos' => Service::orderBy('name')->get(['id', 'name']),
            'prestador_aprovado' => $provider->status === 'approved',
        ]);
    }

    public function show(Request $request, Demand $demand)
    {
        $provider = $request->user();
        $demand->load(['company:id,trade_name', 'service:id,name,requires_license,provider_rate']);

        $distance = null;
        if ($provider->latitude && $provider->longitude && $demand->latitude && $demand->longitude) {
            $distance = Geo::distanceKm($provider->latitude, $provider->longitude, $demand->latitude, $demand->longitude);
        }

        $proposal = Proposal::where('demand_id', $demand->id)
            ->where('provider_id', $provider->id)
            ->whereNotIn('status', ['rejected_provider'])
            ->latest()
            ->first();

        return response()->json([
            'demanda' => $this->formatDemand($demand, $distance),
            'proposta' => $proposal ? [
                'id' => $proposal->id,
                'status' => $proposal->status,
                'mensagem' => $proposal->message,
            ] : null,
            'prestador_aprovado' => $provider->status === 'approved',
            'prestador_tem_cnh' => (bool) $provider->has_license,
        ]);
    }

    public function enviarProposta(Request $request)
    {
        $provider = $request->user();

        if ($provider->status !== 'approved') {
            return response()->json(['message' => 'Seu cadastro ainda não foi aprovado.'], 422);
        }

        $data = $request->validate([
            'demand_id' => 'required|exists:demands,id',
            'message' => 'nullable|string|max:1000',
            'had_recent_surgery' => 'required|boolean',
            'surgery_description' => 'nullable|string|max:1000',
            'health_consent' => 'required_if:had_recent_surgery,true|boolean',
        ]);

        if ($data['had_recent_surgery'] && !($data['health_consent'] ?? false)) {
            return response()->json(['message' => 'É necessário autorizar o compartilhamento dessa informação com a empresa para prosseguir.'], 422);
        }

        $demand = Demand::with('service')->findOrFail($data['demand_id']);

        if (!in_array($demand->status, ['open', 'partially_scheduled'])) {
            return response()->json(['message' => 'Esta demanda não está mais disponível.'], 422);
        }

        if ($demand->date->isPast() && !$demand->date->isToday()) {
            return response()->json(['message' => 'Esta demanda já passou.'], 422);
        }

        if ($demand->service->requires_license && !$provider->has_license) {
            return response()->json(['message' => 'Este serviço exige CNH. Atualize seu perfil.'], 422);
        }

        $exists = Proposal::where('demand_id', $demand->id)
            ->where('provider_id', $provider->id)
            ->whereNotIn('status', ['rejected_provider'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Você já enviou uma proposta para esta demanda.'], 422);
        }

        $proposal = Proposal::create([
            'demand_id' => $demand->id,
            'provider_id' => $provider->id,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'had_recent_surgery' => $data['had_recent_surgery'],
            'surgery_description' => $data['had_recent_surgery'] ? ($data['surgery_description'] ?? null) : null,
            'health_consent' => $data['had_recent_surgery'] ? true : false,
        ]);

        return response()->json(['message' => 'Proposta enviada com sucesso!', 'proposta_id' => $proposal->id], 201);
    }

    private function formatDemand(Demand $d, ?float $distance = null): array
    {
        return [
            'id' => $d->id,
            'titulo' => $d->title,
            'descricao' => $d->description,
            'data' => $d->date?->toDateString(),
            'hora_inicio' => $d->start_time,
            'hora_fim' => $d->end_time,
            'vagas_necessarias' => $d->slots_needed,
            'vagas_confirmadas' => $d->slots_confirmed,
            'servico' => $d->service?->name,
            'servico_id' => $d->service_id,
            'exige_cnh' => (bool) $d->service?->requires_license,
            'valor_hora' => $d->service?->provider_rate,
            'empresa' => $d->company?->trade_name,
            'cidade' => $d->city,
            'bairro' => $d->neighborhood,
            'estado' => $d->state,
            'distancia_km' => $d->distance_km ?? $distance,
        ];
    }
}
