<?php

use App\Models\Proposal;
use Illuminate\Support\Facades\Broadcast;

// Canal privado de chat por proposta e por parte (prestador↔suporte / empresa↔suporte)
// Acesso permitido para o dono da conversa (prestador ou empresa) OU um admin (suporte)
Broadcast::channel('proposal.{proposalId}.{threadType}', function ($user, $proposalId, $threadType) {
    if (!in_array($threadType, ['provider', 'company'])) return false;

    $proposal = Proposal::with('demand')->find($proposalId);
    if (!$proposal) return false;

    if ($threadType === 'provider') {
        $provider = auth('provider')->user();
        if ($provider && $proposal->provider_id == $provider->id) {
            return ['id' => $provider->id, 'name' => $provider->name, 'type' => 'provider'];
        }
    }

    if ($threadType === 'company') {
        $company = auth('company')->user();
        if ($company && $proposal->demand->company_id == $company->id) {
            return ['id' => $company->id, 'name' => $company->trade_name, 'type' => 'company'];
        }
    }

    $admin = auth('admin')->user();
    if ($admin) {
        return ['id' => $admin->id, 'name' => 'Suporte FreeFlex', 'type' => 'admin'];
    }

    return false;
}, ['guards' => ['provider', 'company', 'admin']]);
