<?php

namespace App\Http\Controllers\Concerns;

use App\Services\DocumentTypeClassifier;
use App\Services\GoogleVisionOcrService;
use Illuminate\Http\UploadedFile;

trait ValidatesDocumentType
{
    private const TYPE_LABELS = [
        DocumentTypeClassifier::RG        => 'RG',
        DocumentTypeClassifier::CNH       => 'CNH',
        DocumentTypeClassifier::CNPJ_CARD => 'Cartão CNPJ',
    ];

    /**
     * @return array{ok: bool, status: 'valid'|'unchecked', detected: ?string, message: ?string}
     */
    private function validarTipoDocumento(UploadedFile $file, string $expectedType): array
    {
        $texto = app(GoogleVisionOcrService::class)->extractText($file);

        if ($texto === null || trim($texto) === '') {
            return ['ok' => true, 'status' => 'unchecked', 'detected' => null, 'message' => null];
        }

        $resultado = app(DocumentTypeClassifier::class)->validate($texto, $expectedType);

        if ($resultado['status'] === 'invalid') {
            $esperadoLabel  = self::TYPE_LABELS[$expectedType] ?? $expectedType;
            $detectadoLabel = self::TYPE_LABELS[$resultado['detected']] ?? $resultado['detected'];

            return [
                'ok'      => false,
                'status'  => 'invalid',
                'detected'=> $resultado['detected'],
                'message' => "O arquivo enviado parece ser um(a) {$detectadoLabel}, mas aqui é esperado um(a) {$esperadoLabel}. Envie o documento correto.",
            ];
        }

        return ['ok' => true, 'status' => 'valid', 'detected' => $resultado['detected'], 'message' => null];
    }

    private function registrarValidacaoDocumento(array $atual, string $campo, array $resultado): array
    {
        $atual[$campo] = [
            'status'     => $resultado['status'],
            'detected'   => $resultado['detected'],
            'checked_at' => now()->toIso8601String(),
        ];

        return $atual;
    }
}
