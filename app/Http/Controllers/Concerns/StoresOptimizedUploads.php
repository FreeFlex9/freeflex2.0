<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
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

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png'  => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default      => null,
        };

        // Fallback se GD não conseguir criar a imagem
        if (!$source) {
            return $file->store($directory, 'public');
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

        imagewebp($source, $fullPath, 82);
        imagedestroy($source);

        return $path;
    }
}
