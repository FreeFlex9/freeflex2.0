<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentosController extends Controller
{
    private const CAMPOS_PRESTADOR = [
        'rg_front_path', 'rg_back_path',
        'license_front_path', 'license_back_path',
        'ccmei_path', 'address_proof_path', 'ctps_path', 'profile_photo_path',
    ];

    private const CAMPOS_EMPRESA = [
        'cnpj_card_path', 'address_proof_path',
    ];

    public function show(string $tipo, int $id, string $campo)
    {
        $campos = match ($tipo) {
            'prestador' => self::CAMPOS_PRESTADOR,
            'empresa'   => self::CAMPOS_EMPRESA,
            default     => abort(404),
        };

        abort_unless(in_array($campo, $campos, true), 404);

        $model = $tipo === 'prestador'
            ? Provider::findOrFail($id)
            : Company::findOrFail($id);

        $path = $model->{$campo};

        abort_if(empty($path), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->json([
            'url' => URL::temporarySignedRoute(
                'admin.documentos.assinado',
                now()->addMinutes(10),
                ['tipo' => $tipo, 'id' => $id, 'campo' => $campo],
            ),
            'mime_type' => Storage::disk('public')->mimeType($path),
        ]);
    }
}
