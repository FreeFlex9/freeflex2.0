<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicosController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Servicos/Index', [
            'servicos' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
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

        Service::create([
            'name'             => $data['nome'],
            'hourly_rate'      => $data['valor_hora'],
            'provider_rate'    => $data['valor_repasse'],
            'requires_license' => $data['precisa_cnh'] ?? false,
        ]);

        return back()->with('success', 'Serviço cadastrado com sucesso.');
    }

    public function update(Request $request, Service $servico)
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

        $servico->update([
            'name'             => $data['nome'],
            'hourly_rate'      => $data['valor_hora'],
            'provider_rate'    => $data['valor_repasse'],
            'requires_license' => $data['precisa_cnh'] ?? false,
        ]);

        return back()->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(Service $servico)
    {
        if ($servico->demands()->exists()) {
            return back()->withErrors(['msg' => 'Serviço não pode ser excluído pois está em uso em demandas.']);
        }

        $servico->delete();

        return back()->with('success', 'Serviço excluído com sucesso.');
    }
}
