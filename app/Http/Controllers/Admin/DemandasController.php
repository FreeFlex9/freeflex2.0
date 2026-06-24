<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DemandasController extends Controller
{
    public function index(Request $request)
    {
        $query = Demand::with(['company:id,trade_name', 'service:id,name'])
            ->withCount('proposals');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('empresa')) {
            $query->whereHas('company', fn ($q) => $q->where('trade_name', 'like', '%' . $request->empresa . '%'));
        }
        if ($request->filled('servico')) {
            $query->whereHas('service', fn ($q) => $q->where('name', 'like', '%' . $request->servico . '%'));
        }

        $demandas = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Demandas/Index', [
            'demandas' => $demandas,
            'filters'  => $request->only(['status', 'empresa', 'servico']),
        ]);
    }

    public function propostas(Demand $demanda)
    {
        $propostas = Proposal::with(['provider:id,name,cpf,email'])
            ->where('demand_id', $demanda->id)
            ->where('status', 'pending_admin_approval')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Admin/Demandas/Propostas', [
            'demanda'  => $demanda->load(['company:id,trade_name', 'service:id,name']),
            'propostas' => $propostas,
        ]);
    }

    public function aprovarProposta(Request $request, Proposal $proposta)
    {
        abort_if($proposta->status !== 'pending_admin_approval', 422, 'Status inválido.');

        $demanda = $proposta->demand;

        if ($demanda->slots_confirmed >= $demanda->slots_needed) {
            return back()->withErrors(['msg' => 'Vagas já preenchidas para esta demanda.']);
        }

        $horasJa = Schedule::where('provider_id', $proposta->provider_id)
            ->where('date', $demanda->date)
            ->selectRaw('IFNULL(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) / 3600, 0) as total')
            ->value('total') ?? 0;

        $duracao = (strtotime($demanda->end_time) - strtotime($demanda->start_time)) / 3600;

        if (($horasJa + $duracao) > 8) {
            return back()->withErrors(['msg' => "Excede limite de 8h/dia do prestador (já possui {$horasJa}h nessa data)."]);
        }

        try {
            DB::beginTransaction();

            $proposta->update(['status' => 'accepted']);
            $demanda->increment('slots_confirmed');
            $novaQtd = $demanda->slots_confirmed;

            $novoStatus = $novaQtd >= $demanda->slots_needed ? 'scheduled' : 'open';
            $demanda->update(['status' => $novoStatus]);

            if ($novoStatus === 'scheduled') {
                Proposal::where('demand_id', $demanda->id)
                    ->where('id', '!=', $proposta->id)
                    ->where('status', 'pending_admin_approval')
                    ->update(['status' => 'rejected_admin']);
            }

            Schedule::create([
                'provider_id' => $proposta->provider_id,
                'demand_id'   => $demanda->id,
                'date'        => $demanda->date,
                'start_time'  => $demanda->start_time,
                'end_time'    => $demanda->end_time,
                'description' => $demanda->description,
                'status'      => 'scheduled',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Erro ao aprovar: ' . $e->getMessage()]);
        }

        $msg = 'Contratação aprovada e agendada.';
        if ($novoStatus === 'scheduled') {
            $msg .= ' Vagas completas — outras propostas rejeitadas.';
        }

        return back()->with('success', $msg);
    }

    public function rejeitarProposta(Proposal $proposta)
    {
        abort_if($proposta->status !== 'pending_admin_approval', 422, 'Status inválido.');

        $proposta->update(['status' => 'rejected_admin']);

        return back()->with('success', 'Contratação rejeitada.');
    }
}
