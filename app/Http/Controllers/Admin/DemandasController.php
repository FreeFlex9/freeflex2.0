<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Demanda;
use App\Models\Proposta;
use App\Models\User;
use App\Models\Servico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DemandasController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with(['empresa:id,nome_fantasia', 'servico:id,nome'])
            ->withCount('propostas');

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }
        if ($request->filled('servico_id')) {
            $query->where('servico_id', $request->servico_id);
        }

        $demandas = $query->orderByDesc('created_at')->get();

        return Inertia::render('Admin/Demandas/Index', [
            'demandas'  => $demandas,
            'empresas'  => User::where('role', 'empresa')->orderBy('nome_fantasia')->get(['id', 'nome_fantasia']),
            'servicos'  => Servico::orderBy('nome')->get(['id', 'nome']),
            'filters'   => $request->only(['status', 'empresa_id', 'servico_id']),
        ]);
    }

    public function propostas()
    {
        $propostas = Proposta::with([
            'demanda:id,data,hora_inicio,hora_fim,local,descricao,empresa_id,servico_id,quantidade_necessaria,quantidade_confirmada',
            'demanda.empresa:id,nome_fantasia',
            'demanda.servico:id,nome',
            'prestador:id,name,email',
        ])
            ->where('status', 'pendente_aprovacao_admin')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Admin/Demandas/Propostas', [
            'propostas' => $propostas,
        ]);
    }

    public function aprovarProposta(Request $request, Proposta $proposta)
    {
        abort_if($proposta->status !== 'pendente_aprovacao_admin', 422, 'Proposta em status inválido.');

        $demanda = $proposta->demanda;

        // Verifica vagas
        if ($demanda->quantidade_confirmada >= $demanda->quantidade_necessaria) {
            return back()->withErrors(['msg' => 'Vagas já preenchidas para esta demanda.']);
        }

        // Verifica limite de 8h/dia
        $horasJa = Agendamento::where('prestador_id', $proposta->prestador_id)
            ->where('data', $demanda->data)
            ->selectRaw('IFNULL(SUM(TIME_TO_SEC(TIMEDIFF(hora_fim, hora_inicio))) / 3600, 0) as total')
            ->value('total') ?? 0;

        $duracao = (strtotime($demanda->hora_fim) - strtotime($demanda->hora_inicio)) / 3600;

        if (($horasJa + $duracao) > 8) {
            return back()->withErrors(['msg' => "Aprovação excede limite de 8h diárias do prestador (já possui {$horasJa}h)."]);
        }

        try {
            \DB::beginTransaction();

            $proposta->update(['status' => 'aceita']);

            $novaQtd = $demanda->quantidade_confirmada + 1;
            $demanda->increment('quantidade_confirmada');

            $novoStatus = $novaQtd >= $demanda->quantidade_necessaria ? 'agendada' : 'parcialmente_agendada';
            $demanda->update(['status' => $novoStatus]);

            if ($novoStatus === 'agendada') {
                Proposta::where('demanda_id', $demanda->id)
                    ->where('id', '!=', $proposta->id)
                    ->where('status', 'pendente_aprovacao_admin')
                    ->update(['status' => 'rejeitada_admin']);
            }

            Agendamento::create([
                'prestador_id' => $proposta->prestador_id,
                'demanda_id'   => $demanda->id,
                'data'         => $demanda->data,
                'hora_inicio'  => $demanda->hora_inicio,
                'hora_fim'     => $demanda->hora_fim,
                'descricao'    => $demanda->descricao,
                'origem'       => 'proposta',
            ]);

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return back()->withErrors(['msg' => 'Erro ao aprovar: ' . $e->getMessage()]);
        }

        $msg = 'Contratação aprovada e agendada.';
        if ($novoStatus === 'agendada') {
            $msg .= ' Vagas completas — outras propostas pendentes foram rejeitadas.';
        }

        return back()->with('success', $msg);
    }

    public function rejeitarProposta(Request $request, Proposta $proposta)
    {
        abort_if($proposta->status !== 'pendente_aprovacao_admin', 422, 'Proposta em status inválido.');

        $proposta->update(['status' => 'rejeitada_admin']);

        return back()->with('success', 'Contratação rejeitada.');
    }
}
