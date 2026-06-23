<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Demanda;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DemandasController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with(['empresa:id,nome_fantasia', 'servico:id,nome'])
            ->withCount('propostas');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('empresa')) {
            $query->whereHas('empresa', fn ($q) => $q->where('nome_fantasia', 'like', '%' . $request->empresa . '%'));
        }
        if ($request->filled('servico')) {
            $query->whereHas('servico', fn ($q) => $q->where('nome', 'like', '%' . $request->servico . '%'));
        }

        $demandas = $query->orderByDesc('criado_em')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Demandas/Index', [
            'demandas' => $demandas,
            'filters'  => $request->only(['status', 'empresa', 'servico']),
        ]);
    }

    public function propostas(Demanda $demanda)
    {
        $propostas = Proposta::with(['prestador:id,nome,cpf,email'])
            ->where('demanda_id', $demanda->id)
            ->where('status', 'pendente_aprovacao_admin')
            ->orderBy('enviado_em')
            ->get();

        return Inertia::render('Admin/Demandas/Propostas', [
            'demanda'  => $demanda->load(['empresa:id,nome_fantasia', 'servico:id,nome']),
            'propostas' => $propostas,
        ]);
    }

    public function aprovarProposta(Request $request, Proposta $proposta)
    {
        abort_if($proposta->status !== 'pendente_aprovacao_admin', 422, 'Status inválido.');

        $demanda = $proposta->demanda;

        if ($demanda->quantidade_confirmada >= $demanda->quantidade_necessaria) {
            return back()->withErrors(['msg' => 'Vagas já preenchidas para esta demanda.']);
        }

        // Valida limite de 8h/dia
        $horasJa = Agendamento::where('prestador_id', $proposta->prestador_id)
            ->where('data', $demanda->data)
            ->selectRaw('IFNULL(SUM(TIME_TO_SEC(TIMEDIFF(hora_fim, hora_inicio))) / 3600, 0) as total')
            ->value('total') ?? 0;

        $duracao = (strtotime($demanda->hora_fim) - strtotime($demanda->hora_inicio)) / 3600;

        if (($horasJa + $duracao) > 8) {
            return back()->withErrors(['msg' => "Excede limite de 8h/dia do prestador (já possui {$horasJa}h nessa data)."]);
        }

        try {
            DB::beginTransaction();

            $proposta->update(['status' => 'aceita']);
            $demanda->increment('quantidade_confirmada');
            $novaQtd = $demanda->quantidade_confirmada;

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
                'tipo'         => 'servico',
                'origem'       => 'proposta',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Erro ao aprovar: ' . $e->getMessage()]);
        }

        $msg = 'Contratação aprovada e agendada.';
        if ($novoStatus === 'agendada') {
            $msg .= ' Quantidade completa — outras propostas pendentes foram rejeitadas.';
        }

        return back()->with('success', $msg);
    }

    public function rejeitarProposta(Proposta $proposta)
    {
        abort_if($proposta->status !== 'pendente_aprovacao_admin', 422, 'Status inválido.');

        $proposta->update(['status' => 'rejeitada_admin']);

        return back()->with('success', 'Contratação rejeitada.');
    }
}
