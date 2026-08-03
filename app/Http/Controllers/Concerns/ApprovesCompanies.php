<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;

trait ApprovesCompanies
{
    private function assertCompanyApprovable(Company $company): void
    {
        abort_if($company->status !== 'pending', 422, 'Status inválido.');
        abort_if(empty($company->cnpj_card_path), 422, 'Cartão CNPJ não enviado.');
        abort_if(empty($company->address_proof_path), 422, 'Comprovante de residência não enviado.');
    }
}
