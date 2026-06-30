<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContratarController extends Controller
{
    public function store(Request $request, Provider $provider)
    {
        $company = Auth::guard('company')->user();

        abort_if($company->status !== 'approved', 403, 'Sua conta não está aprovada.');
        abort_if($provider->status !== 'approved' || !$provider->active, 422, 'Prestador indisponível.');

        $mode = $request->input('mode');

        if ($mode === 'new') {
            $data = $request->validate([
                'service_id'  => 'required|exists:services,id',
                'title'       => 'required|string|max:255',
                'date'        => 'required|date|after_or_equal:today',
                'start_time'  => 'required|date_format:H:i',
                'end_time'    => 'required|date_format:H:i|after:start_time',
                'city'        => 'required|string|max:100',
                'state'       => 'required|string|size:2',
                'description' => 'nullable|string|max:2000',
                'message'     => 'nullable|string|max:500',
            ], [
                'date.after_or_equal' => 'A data não pode ser no passado.',
                'end_time.after'      => 'Horário de fim deve ser depois do início.',
            ]);

            $demand = Demand::create([
                'company_id'   => $company->id,
                'service_id'   => $data['service_id'],
                'title'        => $data['title'],
                'date'         => $data['date'],
                'start_time'   => $data['start_time'],
                'end_time'     => $data['end_time'],
                'city'         => $data['city'],
                'state'        => $data['state'],
                'description'  => $data['description'] ?? null,
                'slots_needed' => 1,
                'status'       => 'open',
            ]);

            Proposal::create([
                'demand_id'   => $demand->id,
                'provider_id' => $provider->id,
                'message'     => $data['message'] ?? '',
                'status'      => 'direct_pending',
                'is_direct'   => true,
            ]);

            return back()->with('success', "Convite enviado para {$provider->name}! Aguardando aceite.");
        }

        if ($mode === 'existing') {
            $request->validate([
                'demand_id' => 'required|integer',
                'message'   => 'nullable|string|max:500',
            ]);

            $demand = Demand::where('id', $request->demand_id)
                ->where('company_id', $company->id)
                ->where('status', 'open')
                ->firstOrFail();

            // Verifica se já enviou convite para este prestador nesta demanda
            $jaEnviou = Proposal::where('demand_id', $demand->id)
                ->where('provider_id', $provider->id)
                ->whereNotIn('status', ['rejected', 'rejected_provider'])
                ->exists();

            if ($jaEnviou) {
                return back()->withErrors(['msg' => 'Já existe um convite ativo para este prestador nesta demanda.']);
            }

            Proposal::create([
                'demand_id'   => $demand->id,
                'provider_id' => $provider->id,
                'message'     => $request->message ?? '',
                'status'      => 'direct_pending',
                'is_direct'   => true,
            ]);

            return back()->with('success', "Convite enviado para {$provider->name}! Aguardando aceite.");
        }

        abort(422, 'Modo de contratação inválido.');
    }
}
