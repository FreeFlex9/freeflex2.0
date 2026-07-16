<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        DB::transaction(function () use ($usuario, $modelClass, $tipo) {
            if ($tipo === 'prestador') {
                $this->liberarVagasDoPrestador($usuario);
            }

            DatabaseNotification::where('notifiable_type', $modelClass)
                ->where('notifiable_id', $usuario->id)
                ->delete();

            $usuario->delete();
        });

        Storage::disk('public')->deleteDirectory(
            $tipo === 'empresa' ? "companies/{$id}" : "providers/{$id}"
        );

        return back()->with('success', "Usuário \"{$nome}\" excluído permanentemente.");
    }

    /**
     * Ao excluir um prestador com propostas aceitas/agendadas, o cascade de FK
     * remove proposals/schedules, mas demands.slots_confirmed e status ficam
     * desatualizados — reabre a vaga para a empresa encontrar outro prestador.
     */
    private function liberarVagasDoPrestador(Provider $provider): void
    {
        $demandIds = $provider->schedules()->pluck('demand_id')->unique();

        $demands = Demand::whereIn('id', $demandIds)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($demands as $demand) {
            $demand->slots_confirmed = max(0, $demand->slots_confirmed - 1);
            if ($demand->status !== 'open') {
                $demand->status = 'open';
            }
            $demand->save();
        }
    }
}
