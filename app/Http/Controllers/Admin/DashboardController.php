<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Empresa;
use App\Models\Prestador;
use App\Models\Proposta;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'empresas_pendentes'    => Empresa::where('status_aprovacao', 'pendente')->count(),
                'prestadores_pendentes' => Prestador::where('status_aprovacao', 'pendente')->count(),
                'propostas_pendentes'   => Proposta::where('status', 'pendente_aprovacao_admin')->count(),
                'demandas_abertas'      => Demanda::where('status', 'aberta')->count(),
                'total_empresas'        => Empresa::count(),
                'total_prestadores'     => Prestador::count(),
            ],
        ]);
    }
}
