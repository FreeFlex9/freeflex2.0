<?php

namespace App\Http\Controllers\Company;

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
        return inertia('Empresa/Perfil', [
            'company' => Auth::guard('company')->user(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $company = Auth::guard('company')->user();

        $data = $request->validate([
            'phone'        => 'nullable|string|max:20',
            'trade_name'   => 'required|string|max:255',
            'legal_name'   => 'nullable|string|max:255',
            'zip_code'     => 'nullable|string|max:10',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:2',
        ]);

        $company->update($data);
        return back()->with('success', 'Informações atualizadas!');
    }

    public function updatePassword(Request $request)
    {
        $company = Auth::guard('company')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'password.confirmed'        => 'A nova senha não confere.',
            'password.min'              => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        if (!Hash::check($request->current_password, $company->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $company->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function uploadDocument(Request $request)
    {
        $company = Auth::guard('company')->user();

        // Quando o arquivo excede post_max_size, o PHP descarta o upload inteiro
        // antes do Laravel processá-lo: $_FILES fica vazio e a regra "required"
        // falharia com uma mensagem genérica, escondendo a causa real.
        if (!$request->hasFile('arquivo') && (int) $request->server('CONTENT_LENGTH') > 0) {
            return back()->withErrors([
                'arquivo' => 'O arquivo enviado é muito grande para o servidor processar. Tente um arquivo menor (máx. 5 MB).',
            ]);
        }

        $request->validate([
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'arquivo.required' => 'Nenhum arquivo foi recebido pelo servidor. Tente novamente.',
            'arquivo.max'      => 'Arquivo muito grande. Máximo 5 MB.',
            'arquivo.mimes'    => 'Somente JPG, PNG, WebP ou PDF.',
        ]);

        if ($company->cnpj_card_path) {
            Storage::disk('public')->delete($company->cnpj_card_path);
        }

        $path = $this->storeOptimized($request->file('arquivo'), "companies/{$company->id}");
        $company->update(['cnpj_card_path' => $path]);

        return back()->with('success', 'Cartão CNPJ enviado com sucesso!');
    }

    private function storeOptimized(UploadedFile $file, string $directory): string
    {
        $mime = $file->getMimeType();

        if ($mime === 'application/pdf') {
            return $file->store($directory, 'public');
        }

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png'  => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default      => null,
        };

        if (!$source) {
            return $file->store($directory, 'public');
        }

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
