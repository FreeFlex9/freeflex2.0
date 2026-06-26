<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

class AvaliacoesController extends Controller
{
    public function index()
    {
        $provider = Auth::guard('provider')->user();

        $ratings = Rating::with(['demand:id,title,date', 'company:id,trade_name'])
            ->where('provider_id', $provider->id)
            ->where('rated_by', 'company')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'score'        => $r->score,
                'comment'      => $r->comment,
                'demand_title' => $r->demand->title ?? '',
                'demand_date'  => $r->demand->date?->format('Y-m-d') ?? '',
                'company_name' => $r->company->trade_name ?? '',
                'created_at'   => $r->created_at->format('d/m/Y'),
            ]);

        $media = $ratings->count() > 0
            ? round($ratings->avg('score'), 1)
            : null;

        return inertia('Prestador/Avaliacoes', [
            'ratings' => $ratings,
            'media'   => $media,
            'total'   => $ratings->count(),
        ]);
    }
}
