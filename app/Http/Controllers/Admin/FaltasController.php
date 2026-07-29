<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Support\FaltaPenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FaltasController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with([
            'provider:id,name,email',
            'demand:id,company_id,title',
            'demand.company:id,trade_name',
        ])->whereIn('no_show_status', ['pending_justification', 'justified', 'unjustified']);

        if ($request->filled('status')) {
            $query->where('no_show_status', $request->status);
        }

        $faltas = $query->orderByDesc('no_show_detected_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Faltas/Index', [
            'faltas'  => $faltas,
            'filtros' => $request->only('status'),
        ]);
    }

    public function aprovar(Schedule $schedule)
    {
        abort_unless($schedule->no_show_status === 'pending_justification' && $schedule->no_show_justification, 422);

        $admin = Auth::guard('admin')->user();

        $schedule->update([
            'no_show_status'               => 'justified',
            'no_show_reviewed_at'          => now(),
            'no_show_reviewed_by_admin_id' => $admin?->id,
        ]);

        return back()->with('success', 'Justificativa aprovada. Nenhuma penalidade aplicada.');
    }

    public function rejeitar(Schedule $schedule)
    {
        abort_unless($schedule->no_show_status === 'pending_justification', 422);

        $admin = Auth::guard('admin')->user();

        FaltaPenaltyService::aplicarPenalidade($schedule, $admin?->id);

        return back()->with('success', 'Justificativa rejeitada. Bloqueio temporário de candidatura aplicado ao prestador.');
    }
}
