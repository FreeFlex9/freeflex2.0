<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresOptimizedUploads
{
    private function storeOptimized(UploadedFile $file, string $directory): string
    {
        $mime = $file->getMimeType();

        // PDFs não são imagens — armazena diretamente
        if ($mime === 'application/pdf') {
            return $file->store($directory, 'public');
        }

        // Qualquer falha na otimização (GD ausente/sem suporte a webp, disco sem
        // permissão, etc.) não pode derrubar o upload — cai para o arquivo original.
        try {
            $path = $this->convertToWebp($file, $mime, $directory);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao otimizar imagem, salvando arquivo original: ' . $e->getMessage());
        }

        return $file->store($directory, 'public');
    }

    private function convertToWebp(UploadedFile $file, string $mime, string $directory): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png'  => imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file->getRealPath()) : false,
            default      => false,
        };

        // Fallback se GD não conseguir criar a imagem
        if (!$source) {
            return null;
        }

        // Preservar transparência de PNG
        if ($mime === 'image/png') {
            imagealphablending($source, false);
            imagesavealpha($source, true);
        }

        $filename = Str::uuid() . '.webp';
        $path     = $directory . '/' . $filename;

        Storage::disk('public')->makeDirectory($directory);
        $fullPath = Storage::disk('public')->path($path);

        try {
            if (!function_exists('imagewebp') || !imagewebp($source, $fullPath, 82)) {
                return null;
            }
        } finally {
            imagedestroy($source);
        }

        return $path;
    }
}
