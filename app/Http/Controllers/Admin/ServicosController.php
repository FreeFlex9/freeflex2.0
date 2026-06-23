<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicosController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Servicos/Index', [
            'servicos' => Servico::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'           => 'required|string|max:255',
            'valor_hora'     => 'required|numeric|min:0.01',
            'valor_repasse'  => 'required|numeric|min:0',
            'precisa_cnh'    => 'boolean',
        ]);

        if ($data['valor_repasse'] > $data['valor_hora']) {
            return back()->withErrors(['valor_repasse' => 'Repasse não pode ser maior que o valor/hora.']);
        }

        Servico::create($data);

        return back()->with('success', 'Serviço cadastrado com sucesso.');
    }

    public function update(Request $request, Servico $servico)
    {
        $data = $request->validate([
            'nome'          => 'required|string|max:255',
            'valor_hora'    => 'required|numeric|min:0.01',
            'valor_repasse' => 'required|numeric|min:0',
            'precisa_cnh'   => 'boolean',
        ]);

        if ($data['valor_repasse'] > $data['valor_hora']) {
            return back()->withErrors(['valor_repasse' => 'Repasse não pode ser maior que o valor/hora.']);
        }

        $servico->update($data);

        return back()->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(Servico $servico)
    {
        if ($servico->demandas()->exists()) {
            return back()->withErrors(['msg' => 'Serviço não pode ser excluído pois está em uso em demandas.']);
        }

        $servico->delete();

        return back()->with('success', 'Serviço excluído com sucesso.');
    }
}
