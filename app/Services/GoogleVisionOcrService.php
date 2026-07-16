<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleVisionOcrService
{
    /**
     * Extrai o texto do documento via Google Cloud Vision.
     * Retorna null se a chave não estiver configurada ou se a API falhar
     * (rede, quota, timeout) — o chamador deve tratar isso como "não verificado",
     * nunca como bloqueio do envio.
     */
    public function extractText(UploadedFile $file): ?string
    {
        $key = config('services.google_vision.key');

        if (empty($key)) {
            return null;
        }

        try {
            $content = base64_encode(file_get_contents($file->getRealPath()));

            $response = $file->getMimeType() === 'application/pdf'
                ? $this->annotateFile($key, $content)
                : $this->annotateImage($key, $content);

            return $response;
        } catch (\Throwable $e) {
            Log::warning('Google Vision OCR indisponível: ' . $e->getMessage());
            return null;
        }
    }

    private function annotateImage(string $key, string $base64Content): ?string
    {
        $resp = Http::timeout(15)->post("https://vision.googleapis.com/v1/images:annotate?key={$key}", [
            'requests' => [[
                'image'    => ['content' => $base64Content],
                'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']],
            ]],
        ]);

        if (!$resp->successful()) {
            Log::warning('Google Vision retornou erro: ' . $resp->status() . ' ' . $resp->body());
            return null;
        }

        return $resp->json('responses.0.fullTextAnnotation.text');
    }

    private function annotateFile(string $key, string $base64Content): ?string
    {
        $resp = Http::timeout(15)->post("https://vision.googleapis.com/v1/files:annotate?key={$key}", [
            'requests' => [[
                'inputConfig' => [
                    'content'  => $base64Content,
                    'mimeType' => 'application/pdf',
                ],
                'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']],
                'pages'    => [1],
            ]],
        ]);

        if (!$resp->successful()) {
            Log::warning('Google Vision retornou erro: ' . $resp->status() . ' ' . $resp->body());
            return null;
        }

        return $resp->json('responses.0.responses.0.fullTextAnnotation.text');
    }
}
