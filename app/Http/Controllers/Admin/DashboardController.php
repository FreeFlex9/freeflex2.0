<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demanda;
use App\Models\Proposta;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'empresas_pendentes'   => User::where('role', 'empresa')->where('status_aprovacao', 'pendente')->count(),
                'prestadores_pendentes' => User::where('role', 'prestador')->where('status_aprovacao', 'pendente')->count(),
                'propostas_pendentes'  => Proposta::where('status', 'pendente_aprovacao_admin')->count(),
                'demandas_abertas'     => Demanda::where('status', 'aberta')->count(),
                'total_empresas'       => User::where('role', 'empresa')->count(),
                'total_prestadores'    => User::where('role', 'prestador')->count(),
            ],
        ]);
    }
}
