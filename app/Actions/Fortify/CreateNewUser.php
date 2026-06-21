<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name'         => ['required', 'string', 'max:255'],
            'cpf'          => ['required', 'string'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'telefone'     => ['required', 'string'],
            'cep'          => ['nullable', 'string'],
            'logradouro'   => ['nullable', 'string', 'max:255'],
            'numero'       => ['nullable', 'string', 'max:20'],
            'complemento'  => ['nullable', 'string', 'max:100'],
            'bairro'       => ['nullable', 'string', 'max:100'],
            'cidade'       => ['nullable', 'string', 'max:100'],
            'estado'       => ['nullable', 'string', 'max:2'],
            'is_mei'       => ['sometimes', 'boolean'],
            'password'     => $this->passwordRules(),
            'terms'        => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $cpfRaw      = preg_replace('/\D/', '', $input['cpf']);
        $telefoneRaw = preg_replace('/\D/', '', $input['telefone']);
        $cepRaw      = preg_replace('/\D/', '', $input['cep'] ?? '');

        if (User::where('cpf', $cpfRaw)->exists()) {
            throw ValidationException::withMessages(['cpf' => 'CPF já cadastrado.']);
        }

        return User::create([
            'name'        => $input['name'],
            'email'       => $input['email'],
            'password'    => Hash::make($input['password']),
            'role'        => 'prestador',
            'cpf'         => $cpfRaw,
            'telefone'    => $telefoneRaw,
            'cep'         => $cepRaw ?: null,
            'logradouro'  => $input['logradouro'] ?? null,
            'numero'      => $input['numero'] ?? null,
            'complemento' => $input['complemento'] ?? null,
            'bairro'      => $input['bairro'] ?? null,
            'cidade'      => $input['cidade'] ?? null,
            'estado'      => $input['estado'] ?? null,
            'is_mei'      => $input['is_mei'] ?? false,
        ]);
    }
}
