<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', $value);

        if (strlen($cpf) !== 11) {
            $fail('CPF inválido.');
            return;
        }

        // Rejeita sequências iguais (111.111.111-11, etc.)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $fail('CPF inválido.');
            return;
        }

        // Primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if (intval($cpf[9]) !== $digit1) {
            $fail('CPF inválido.');
            return;
        }

        // Segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        if (intval($cpf[10]) !== $digit2) {
            $fail('CPF inválido.');
        }
    }
}
