<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function index()
    {
        return inertia('Prestador/Perfil', [
            'provider' => Auth::guard('provider')->user(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'bio'   => 'nullable|string|max:1000',
        ]);

        $provider->update($data);
        return back()->with('success', 'Informações atualizadas!');
    }

    public function updatePassword(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $request->validate([
            'current_password'  => 'required|string',
            'password'          => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'password.confirmed'        => 'A nova senha não confere.',
            'password.min'              => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        if (!Hash::check($request->current_password, $provider->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $provider->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function uploadDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $request->validate([
            'tipo'    => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei',
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'arquivo.max'   => 'Arquivo muito grande. Máximo 5 MB.',
            'arquivo.mimes' => 'Somente JPG, PNG, WebP ou PDF.',
        ]);

        $campoMap = [
            'profile_photo' => 'profile_photo_path',
            'rg_front'      => 'rg_front_path',
            'rg_back'       => 'rg_back_path',
            'license_front' => 'license_front_path',
            'license_back'  => 'license_back_path',
            'ccmei'         => 'ccmei_path',
        ];

        $campo = $campoMap[$request->tipo];

        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
        }

        $path = $this->storeOptimized($request->file('arquivo'), "providers/{$provider->id}");
        $provider->update([$campo => $path]);

        return back()->with('success', 'Documento enviado com sucesso!');
    }

    public function removeDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();
        $request->validate(['tipo' => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei']);

        $campoMap = [
            'profile_photo' => 'profile_photo_path',
            'rg_front'      => 'rg_front_path',
            'rg_back'       => 'rg_back_path',
            'license_front' => 'license_front_path',
            'license_back'  => 'license_back_path',
            'ccmei'         => 'ccmei_path',
        ];

        $campo = $campoMap[$request->tipo];
        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
            $provider->update([$campo => null]);
        }

        return back()->with('success', 'Documento removido.');
    }

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
