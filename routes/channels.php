<?php

use App\Models\Proposal;
use Illuminate\Support\Facades\Broadcast;

// Canal privado de chat por proposta
// Acesso permitido para o prestador da proposta OU a empresa da demanda
Broadcast::channel('proposal.{proposalId}', function ($user, $proposalId) {
    $proposal = Proposal::with('demand')->find($proposalId);
    if (!$proposal) return false;

    // Tenta o guard de prestador
    $provider = auth('provider')->user();
    if ($provider && $proposal->provider_id == $provider->id) {
        return ['id' => $provider->id, 'name' => $provider->name, 'type' => 'provider'];
    }

    // Tenta o guard de empresa
    $company = auth('company')->user();
    if ($company && $proposal->demand->company_id == $company->id) {
        return ['id' => $company->id, 'name' => $company->trade_name, 'type' => 'company'];
    }

    return false;
});
