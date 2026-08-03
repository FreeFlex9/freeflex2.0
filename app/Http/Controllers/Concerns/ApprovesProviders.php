<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Provider;

trait ApprovesProviders
{
    private function assertProviderApprovable(Provider $provider): void
    {
        abort_if($provider->status !== 'pending', 422, 'Status inválido.');

        if ($provider->has_license) {
            if ($provider->is_digital_license) {
                abort_if(empty($provider->license_front_path), 422, 'CNH digital não enviada.');
            } else {
                abort_if(
                    empty($provider->license_front_path) || empty($provider->license_back_path),
                    422, 'CNH (frente e verso) não enviada.'
                );
            }
        } else {
            abort_if(
                empty($provider->rg_front_path) || empty($provider->rg_back_path),
                422, 'RG (frente e verso) não enviado.'
            );
        }

        if (!empty($provider->mei_cnpj) && empty($provider->ccmei_path)) {
            abort(422, 'CCMEI não enviado para MEI.');
        }

        abort_if(empty($provider->address_proof_path), 422, 'Comprovante de residência não enviado.');

        abort_if(empty($provider->ctps_path), 422, 'Carteira de Trabalho (CTPS) não enviada.');
    }

    private function assertProviderCnhApprovable(Provider $provider): void
    {
        abort_if($provider->cnh_status !== 'pending', 422, 'CNH não está pendente.');

        $docsOk = $provider->is_digital_license
            ? !empty($provider->license_front_path)
            : !empty($provider->license_front_path) && !empty($provider->license_back_path);

        abort_if(!$docsOk, 422, 'Documentos de CNH não enviados.');
    }
}
