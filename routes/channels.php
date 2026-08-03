<?php

use App\Models\Admin;
use App\Models\Company;
use App\Models\Demand;
use App\Models\Provider;
use Illuminate\Support\Facades\Broadcast;

// Canal privado de chat por demanda e por parte (prestador↔suporte / empresa↔suporte)
// Acesso permitido para o dono da conversa (prestador ou empresa) OU um admin (suporte)
// $user já vem resolvido pelo Broadcaster a partir dos guards abaixo (incluindo 'sanctum',
// necessário para o app mobile autenticado via token) — não re-resolver guards manualmente aqui.
Broadcast::channel('chat.{demandId}.{threadType}', function ($user, $demandId, $threadType) {
    if (!in_array($threadType, ['provider', 'company'])) return false;

    if ($user instanceof Admin) {
        return ['id' => $user->id, 'name' => 'Suporte FreeFlex', 'type' => 'admin'];
    }

    $demand = Demand::find($demandId);
    if (!$demand) return false;

    if ($threadType === 'provider' && $user instanceof Provider) {
        $hasProposal = $demand->proposals()->where('provider_id', $user->id)->exists();
        if ($hasProposal) {
            return ['id' => $user->id, 'name' => $user->name, 'type' => 'provider'];
        }
    }

    if ($threadType === 'company' && $user instanceof Company) {
        if ($demand->company_id == $user->id) {
            return ['id' => $user->id, 'name' => $user->trade_name, 'type' => 'company'];
        }
    }

    return false;
}, ['guards' => ['provider', 'company', 'admin', 'sanctum']]);

// Canal pessoal para badge de não lidos (central de mensagens)
Broadcast::channel('chat-inbox.provider.{id}', function ($user, $id) {
    return $user instanceof Provider && $user->id == $id;
}, ['guards' => ['provider', 'sanctum']]);

Broadcast::channel('chat-inbox.company.{id}', function ($user, $id) {
    return $user instanceof Company && $user->id == $id;
}, ['guards' => ['company', 'sanctum']]);

Broadcast::channel('chat-inbox.admin', function ($user) {
    return $user instanceof Admin;
}, ['guards' => ['admin', 'sanctum']]);
