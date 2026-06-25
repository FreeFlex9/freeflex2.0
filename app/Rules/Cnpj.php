<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', $value);

        if (strlen($cnpj) !== 14) {
            $fail('CNPJ inválido.');
            return;
        }

        // Rejeita sequências iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $fail('CNPJ inválido.');
            return;
        }

        // Primeiro dígito verificador
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if (intval($cnpj[12]) !== $digit1) {
            $fail('CNPJ inválido.');
            return;
        }

        // Segundo dígito verificador
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        if (intval($cnpj[13]) !== $digit2) {
            $fail('CNPJ inválido.');
        }
    }
}
