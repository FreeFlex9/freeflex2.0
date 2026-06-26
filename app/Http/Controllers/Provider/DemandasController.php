<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandasController extends Controller
{
    public function index(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $query = Demand::with(['company:id,trade_name', 'service:id,name,requires_license,provider_rate'])
            ->whereIn('status', ['open', 'partially_scheduled'])
            ->whereDate('date', '>=', now()->toDateString())
            // Se prestador não tem CNH, oculta demandas que exigem CNH
            ->when(!$provider->has_license, function ($q) {
                $q->whereHas('service', fn ($s) => $s->where('requires_license', false));
            })
            // Não mostra demandas em que o prestador já enviou proposta
            ->whereNotExists(function ($sub) use ($provider) {
                $sub->from('proposals')
                    ->whereColumn('proposals.demand_id', 'demands.id')
                    ->where('proposals.provider_id', $provider->id)
                    ->whereNotIn('proposals.status', ['rejected_provider']);
            });

        // Filtro: apenas meus serviços
        if ($request->boolean('my_services')) {
            $serviceIds = $provider->services()->pluck('services.id');
            $query->whereIn('service_id', $serviceIds);
        }

        // Filtro: serviço específico
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filtro: texto livre (título, descrição, cidade)
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', $q)
                    ->orWhere('description', 'like', $q)
                    ->orWhere('city', 'like', $q)
                    ->orWhere('neighborhood', 'like', $q);
            });
        }

        // Filtro: cidade
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $demands = $query->orderBy('date')->orderBy('start_time')->get();

        // Calcular distância se prestador tem coordenadas
        if ($provider->latitude && $provider->longitude) {
            $demands = $demands->map(function ($d) use ($provider) {
                if ($d->latitude && $d->longitude) {
                    $d->distance_km = $this->haversine(
                        $provider->latitude, $provider->longitude,
                        $d->latitude, $d->longitude
                    );
                } else {
                    $d->distance_km = null;
                }
                return $d;
            });

            // Ordenar por distância se solicitado
            if ($request->get('sort') === 'distance') {
                $demands = $demands->sortBy(fn ($d) => $d->distance_km ?? 9999)->values();
            }
        }

        // Serviços do prestador para filtros
        $myServices = $provider->services()->orderBy('name')->get(['services.id', 'services.name']);

        // Todos os serviços disponíveis (para o filtro)
        $allServices = \App\Models\Service::orderBy('name')->get(['id', 'name']);

        return inertia('Prestador/BuscarDemandas', [
            'demands'         => $demands,
            'myServices'      => $myServices,
            'allServices'     => $allServices,
            'filters'         => $request->only(['q', 'city', 'service_id', 'my_services', 'sort']),
            'providerApproved'=> $provider->status === 'approved',
        ]);
    }

    public function enviarProposta(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        if ($provider->status !== 'approved') {
            return back()->withErrors(['error' => 'Seu cadastro ainda não foi aprovado.']);
        }

        $data = $request->validate([
            'demand_id' => 'required|exists:demands,id',
            'message'   => 'nullable|string|max:1000',
        ]);

        $demand = Demand::with('service')->findOrFail($data['demand_id']);

        // Valida status da demanda
        if (!in_array($demand->status, ['open', 'partially_scheduled'])) {
            return back()->withErrors(['error' => 'Esta demanda não está mais disponível.']);
        }

        // Valida data
        if ($demand->date->isPast() && $demand->date->isToday() === false) {
            return back()->withErrors(['error' => 'Esta demanda já passou.']);
        }

        // Valida CNH se necessário
        if ($demand->service->requires_license && !$provider->has_license) {
            return back()->withErrors(['error' => 'Este serviço exige CNH. Atualize seu perfil.']);
        }

        // Verifica se já enviou proposta
        $exists = Proposal::where('demand_id', $demand->id)
            ->where('provider_id', $provider->id)
            ->whereNotIn('status', ['rejected_provider'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Você já enviou uma proposta para esta demanda.']);
        }

        Proposal::create([
            'demand_id'   => $demand->id,
            'provider_id' => $provider->id,
            'message'     => $data['message'] ?? null,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Proposta enviada com sucesso!');
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
