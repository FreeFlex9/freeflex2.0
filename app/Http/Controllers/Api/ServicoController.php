<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ServicoController extends Controller
{
    public function index()
    {
        return Service::all()->map(fn (Service $s) => [
            'id' => $s->id,
            'nome' => $s->name,
            'valor_hora' => (string) $s->hourly_rate,
            'precisa_cnh' => $s->requires_license,
        ]);
    }
}
