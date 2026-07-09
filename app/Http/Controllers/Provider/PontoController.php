<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Support\Geo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PontoController extends Controller
{
    private const MAX_DISTANCE_METERS = 300;

    public function index(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $data = $request->filled('data')
            ? Carbon::createFromFormat('Y-m-d', $request->input('data'))->startOfDay()
            : now()->startOfDay();

        $schedules = Schedule::with([
            'demand.company:id,trade_name',
            'demand.service:id,name',
        ])
            ->where('provider_id', $provider->id)
            ->whereDate('date', $data->toDateString())
            ->orderBy('start_time')
            ->get()
            ->map(fn (Schedule $s) => [
                'id'                   => $s->id,
                'date'                 => $s->date->format('Y-m-d'),
                'start_time'           => substr($s->start_time, 0, 5),
                'end_time'             => substr($s->end_time, 0, 5),
                'status'               => $s->status,
                'company_name'         => $s->demand->company->trade_name ?? '',
                'service_name'         => $s->demand->service->name ?? '',
                'address'              => trim(collect([
                    $s->demand->street ? "{$s->demand->street}, {$s->demand->number}" : null,
                    $s->demand->neighborhood,
                    $s->demand->city && $s->demand->state ? "{$s->demand->city}/{$s->demand->state}" : null,
                ])->filter()->implode(' — ')),
                'has_location'         => (bool) ($s->demand->latitude && $s->demand->longitude),
                'check_in_at'          => $s->check_in_at?->format('H:i'),
                'check_in_distance_m'  => $s->check_in_distance_m,
                'check_out_at'         => $s->check_out_at?->format('H:i'),
                'check_out_distance_m' => $s->check_out_distance_m,
            ]);

        return inertia('Prestador/Ponto', [
            'schedules' => $schedules,
            'data'      => $data->toDateString(),
        ]);
    }

    public function checkin(Request $request, Schedule $schedule)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($schedule->provider_id !== $provider->id, 403);

        $coords = $this->validateCoords($request);

        if ($schedule->status !== 'scheduled') {
            return back()->withErrors(['error' => 'Este agendamento não está disponível para check-in.']);
        }
        if ($schedule->check_in_at) {
            return back()->withErrors(['error' => 'Check-in já realizado para este agendamento.']);
        }
        if (!$schedule->date->isToday()) {
            return back()->withErrors(['error' => 'Check-in só é permitido no dia do agendamento.']);
        }

        $distanceM = $this->distanceToDemand($schedule, $coords['latitude'], $coords['longitude']);

        if ($distanceM !== null && $distanceM > self::MAX_DISTANCE_METERS) {
            return back()->withErrors(['error' => "Você está a {$distanceM}m do local do serviço. Aproxime-se para fazer o check-in."]);
        }

        $schedule->update([
            'check_in_at'         => now(),
            'check_in_lat'        => $coords['latitude'],
            'check_in_lng'        => $coords['longitude'],
            'check_in_distance_m' => $distanceM,
        ]);

        return back()->with('success', 'Check-in registrado com sucesso!');
    }

    public function checkout(Request $request, Schedule $schedule)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($schedule->provider_id !== $provider->id, 403);

        $coords = $this->validateCoords($request);

        if (!$schedule->check_in_at) {
            return back()->withErrors(['error' => 'Faça o check-in antes de dar check-out.']);
        }
        if ($schedule->check_out_at) {
            return back()->withErrors(['error' => 'Check-out já realizado para este agendamento.']);
        }

        $distanceM = $this->distanceToDemand($schedule, $coords['latitude'], $coords['longitude']);

        if ($distanceM !== null && $distanceM > self::MAX_DISTANCE_METERS) {
            return back()->withErrors(['error' => "Você está a {$distanceM}m do local do serviço. Aproxime-se para fazer o check-out."]);
        }

        $schedule->update([
            'check_out_at'         => now(),
            'check_out_lat'        => $coords['latitude'],
            'check_out_lng'        => $coords['longitude'],
            'check_out_distance_m' => $distanceM,
            'status'               => 'completed',
        ]);

        return back()->with('success', 'Check-out registrado com sucesso!');
    }

    private function validateCoords(Request $request): array
    {
        return $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
    }

    private function distanceToDemand(Schedule $schedule, float $lat, float $lng): ?int
    {
        $demand = $schedule->demand;

        if (!$demand->latitude || !$demand->longitude) {
            return null;
        }

        return (int) round(Geo::distanceKm($lat, $lng, $demand->latitude, $demand->longitude) * 1000);
    }
}
