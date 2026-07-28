<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeusServicosController extends Controller
{
    public function index()
    {
        $provider = Auth::guard('provider')->user();

        $myIds    = $provider->services()->pluck('services.id')->toArray();
        $services = Service::orderBy('name')->get();

        return inertia('Prestador/MeusServicos', [
            'services' => $services,
            'myIds'    => $myIds,
        ]);
    }

    public function toggle(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $request->validate(['service_id' => 'required|exists:services,id']);

        $serviceId = $request->service_id;
        $service   = Service::findOrFail($serviceId);

        // Bloqueia serviços que exigem CNH se o prestador não tem
        if ($service->requires_license && !$provider->has_license) {
            return back()->withErrors(['msg' => 'Este serviço exige CNH e você não possui habilitação cadastrada.']);
        }

        $jaTem = $provider->services()->where('services.id', $serviceId)->exists();

        if ($jaTem) {
            $vinculoAtivo = Proposal::where('provider_id', $provider->id)
                ->whereIn('status', ['pending', 'pending_admin_approval', 'accepted'])
                ->whereHas('demand', fn ($q) => $q->where('service_id', $serviceId)
                    ->whereNotIn('status', ['completed', 'cancelled']))
                ->exists();

            if ($vinculoAtivo) {
                return back()->withErrors(['msg' => 'Não é possível remover este serviço: há proposta(s) ou agendamento(s) ativos vinculados a ele.']);
            }
        }

        $provider->services()->toggle($serviceId);

        return back()->with('success', 'Serviço atualizado!');
    }
}
