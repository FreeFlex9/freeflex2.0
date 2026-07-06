<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Proposal;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $tipo   = $request->input('tipo') === 'empresa' ? 'empresa' : 'prestador';
        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->input('status')
            : '';
        $search = trim((string) $request->input('search', ''));

        $modelClass = $tipo === 'empresa' ? Company::class : Provider::class;
        $nameColumn = $tipo === 'empresa' ? 'trade_name' : 'name';
        $docColumn  = $tipo === 'empresa' ? 'cnpj' : 'cpf';

        $query = $modelClass::query();

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $looksLikeDocOrPhone = (bool) preg_match('/^[\d.\-\/\s()]+$/', $search);
            $digits = $looksLikeDocOrPhone ? preg_replace('/\D/', '', $search) : '';

            $query->where(function ($q) use ($search, $digits, $nameColumn, $docColumn) {
                $q->where($nameColumn, 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($digits !== '') {
                    $q->orWhere($docColumn, 'like', "%{$digits}%")
                        ->orWhere('phone', 'like', "%{$digits}%");
                }
            });
        }

        $columns = $tipo === 'empresa'
            ? ['id', 'trade_name', 'cnpj', 'email', 'phone', 'status', 'city', 'state', 'created_at']
            : ['id', 'name', 'cpf', 'email', 'phone', 'status', 'city', 'state', 'created_at'];

        $usuarios = $query->orderBy($nameColumn)
            ->paginate(15, $columns)
            ->withQueryString();

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'filtros'  => ['tipo' => $tipo, 'status' => $status, 'search' => $search],
            'pendentes' => [
                'empresas'    => Company::where('status', 'pending')->count(),
                'prestadores' => Provider::where('status', 'pending')->count(),
                'propostas'   => Proposal::where('status', 'pending_admin_approval')->count(),
            ],
        ]);
    }
}
