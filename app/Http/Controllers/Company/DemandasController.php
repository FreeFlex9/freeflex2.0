<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandasController extends Controller
{
    public function index()
    {
        $company = Auth::guard('company')->user();

        $demands = Demand::with(['service:id,name', 'proposals' => fn ($q) => $q->where('status', 'pending')])
            ->withCount([
                'proposals',
                'proposals as pending_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->where('company_id', $company->id)
            ->orderByDesc('date')
            ->get();

        return inertia('Empresa/MinhasDemandas', [
            'demands' => $demands,
        ]);
    }

    public function create()
    {
        $services = Service::orderBy('name')->get(['id', 'name', 'requires_license']);

        return inertia('Empresa/NovaDemanda', [
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $company = Auth::guard('company')->user();

        abort_if($company->status !== 'approved', 403, 'Conta não aprovada.');

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'service_id'   => 'required|exists:services,id',
            'date'         => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'slots_needed' => 'required|integer|min:1|max:50',
            'description'  => 'nullable|string|max:2000',
            'zip_code'     => 'nullable|string|max:9',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|size:2',
        ], [
            'date.after_or_equal' => 'A data não pode ser no passado.',
            'end_time.after'      => 'O horário de fim deve ser depois do início.',
            'city.required'       => 'A cidade é obrigatória.',
            'state.required'      => 'O estado é obrigatório.',
        ]);

        $data['company_id'] = $company->id;
        $data['status']     = 'open';

        Demand::create($data);

        return redirect()->route('empresa.demandas.index')
            ->with('success', 'Demanda publicada com sucesso!');
    }

    public function show(Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);

        $demand->load(['service:id,name,requires_license']);

        $proposals = Proposal::with(['provider:id,name,cpf,email,phone,bio,profile_photo_path,has_license,is_pcd,pcd_type'])
            ->where('demand_id', $demand->id)
            ->whereNotIn('status', ['rejected_provider'])
            ->orderBy('created_at')
            ->get();

        return inertia('Empresa/VerDemanda', [
            'demand'    => $demand,
            'proposals' => $proposals,
        ]);
    }

    public function edit(Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);
        abort_if(!in_array($demand->status, ['open']), 422, 'Apenas demandas abertas podem ser editadas.');

        $services = Service::orderBy('name')->get(['id', 'name', 'requires_license']);

        return inertia('Empresa/EditarDemanda', [
            'demand'   => $demand,
            'services' => $services,
        ]);
    }

    public function update(Request $request, Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);
        abort_if($demand->status !== 'open', 422, 'Apenas demandas abertas podem ser editadas.');

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'service_id'   => 'required|exists:services,id',
            'date'         => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'slots_needed' => 'required|integer|min:1|max:50',
            'description'  => 'nullable|string|max:2000',
            'zip_code'     => 'nullable|string|max:9',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|size:2',
        ], [
            'date.after_or_equal' => 'A data não pode ser no passado.',
            'end_time.after'      => 'O horário de fim deve ser depois do início.',
        ]);

        $demand->update($data);

        return redirect()->route('empresa.demandas.show', $demand->id)
            ->with('success', 'Demanda atualizada!');
    }

    public function destroy(Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);
        abort_if(!in_array($demand->status, ['open', 'partially_scheduled']), 422, 'Não é possível cancelar esta demanda.');

        $demand->update(['status' => 'cancelled']);
        return back()->with('success', 'Demanda cancelada.');
    }
}
