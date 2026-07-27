<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Proposal;
use App\Models\Provider;
use App\Models\UserBlockLog;
use App\Support\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $tipo = in_array($request->input('tipo'), ['prestador', 'empresa', 'todos'], true)
            ? $request->input('tipo')
            : 'todos';
        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->input('status')
            : '';
        $search = trim((string) $request->input('search', ''));

        $query = match ($tipo) {
            'prestador' => $this->queryPrestadores($status, $search),
            'empresa'   => $this->queryEmpresas($status, $search),
            default     => $this->queryPrestadores($status, $search)
                ->unionAll($this->queryEmpresas($status, $search)),
        };

        $usuarios = $query->orderBy('nome')
            ->paginate(15)
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

    private function queryPrestadores(string $status, string $search)
    {
        $query = DB::table('providers')->select([
            'id',
            'name as nome',
            'cpf as documento',
            'email',
            'phone',
            'city',
            'state',
            'status',
            'active',
            'blocked_until',
            'block_reason',
            'created_at',
            DB::raw("'prestador' as tipo"),
        ]);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $this->aplicarBusca($query, $search, 'name', 'cpf');
        }

        return $query;
    }

    private function queryEmpresas(string $status, string $search)
    {
        $query = DB::table('companies')->select([
            'id',
            'trade_name as nome',
            'cnpj as documento',
            'email',
            'phone',
            'city',
            'state',
            'status',
            'active',
            'blocked_until',
            'block_reason',
            'created_at',
            DB::raw("'empresa' as tipo"),
        ]);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $this->aplicarBusca($query, $search, 'trade_name', 'cnpj');
        }

        return $query;
    }

    private function aplicarBusca($query, string $search, string $nameColumn, string $docColumn): void
    {
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

    public function destroy(string $tipo, int $id)
    {
        abort_unless(in_array($tipo, ['prestador', 'empresa'], true), 404);

        $modelClass = $tipo === 'empresa' ? Company::class : Provider::class;
        $usuario    = $modelClass::findOrFail($id);
        $nome       = $tipo === 'empresa' ? $usuario->trade_name : $usuario->name;
        $admin      = Auth::guard('admin')->user();

        AccountDeletionService::excluir($usuario, $tipo, adminId: $admin?->id, adminEmail: $admin?->email);

        return back()->with('success', "Usuário \"{$nome}\" excluído permanentemente.");
    }

    public function bloquear(string $tipo, int $id, Request $request)
    {
        abort_unless(in_array($tipo, ['prestador', 'empresa'], true), 404);

        $data = $request->validate([
            'tipo_bloqueio'  => 'required|in:temporario,definitivo',
            'motivo'         => 'required|string|max:1000',
            'bloqueado_ate'  => 'required_if:tipo_bloqueio,temporario|nullable|date|after:now',
        ]);

        $modelClass = $tipo === 'empresa' ? Company::class : Provider::class;
        $usuario    = $modelClass::findOrFail($id);
        $nome       = $tipo === 'empresa' ? $usuario->trade_name : $usuario->name;
        $admin      = Auth::guard('admin')->user();
        $definitivo = $data['tipo_bloqueio'] === 'definitivo';
        $ate        = $definitivo ? null : $data['bloqueado_ate'];

        DB::transaction(function () use ($usuario, $tipo, $nome, $admin, $definitivo, $ate, $data) {
            $usuario->update([
                'active'              => false,
                'blocked_at'          => now(),
                'blocked_until'       => $ate,
                'block_reason'        => $data['motivo'],
                'blocked_by_admin_id' => $admin?->id,
            ]);

            UserBlockLog::create([
                'tipo'          => $tipo,
                'usuario_id'    => $usuario->id,
                'nome'          => $nome,
                'email'         => $usuario->email,
                'acao'          => $definitivo ? 'bloqueio_definitivo' : 'bloqueio_temporario',
                'motivo'        => $data['motivo'],
                'blocked_until' => $ate,
                'admin_id'      => $admin?->id,
                'created_at'    => now(),
            ]);
        });

        return back()->with('success', "Usuário \"{$nome}\" bloqueado " . ($definitivo ? 'permanentemente.' : 'temporariamente.'));
    }

    public function desbloquear(string $tipo, int $id)
    {
        abort_unless(in_array($tipo, ['prestador', 'empresa'], true), 404);

        $modelClass = $tipo === 'empresa' ? Company::class : Provider::class;
        $usuario    = $modelClass::findOrFail($id);
        $nome       = $tipo === 'empresa' ? $usuario->trade_name : $usuario->name;
        $admin      = Auth::guard('admin')->user();

        DB::transaction(function () use ($usuario, $tipo, $nome, $admin) {
            $usuario->update([
                'active'              => true,
                'blocked_at'          => null,
                'blocked_until'       => null,
                'block_reason'        => null,
                'blocked_by_admin_id' => null,
            ]);

            UserBlockLog::create([
                'tipo'          => $tipo,
                'usuario_id'    => $usuario->id,
                'nome'          => $nome,
                'email'         => $usuario->email,
                'acao'          => 'desbloqueio',
                'motivo'        => null,
                'blocked_until' => null,
                'admin_id'      => $admin?->id,
                'created_at'    => now(),
            ]);
        });

        return back()->with('success', "Usuário \"{$nome}\" desbloqueado.");
    }
}
