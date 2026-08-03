<?php

namespace App\Services;

use App\Events\ChatInboxPing;
use App\Events\MessageSent;
use App\Models\Demand;
use App\Models\Message;
use App\Models\Proposal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public const PROVIDER_ELIGIBLE_STATUSES = ['pending', 'pending_company_accept', 'pending_admin_approval', 'accepted'];
    public const COMPANY_ELIGIBLE_STATUSES = ['pending', 'pending_admin_approval', 'accepted'];

    public function threadMessages(int $demandId, string $partyType, int $partyId, string $readerRole): Collection
    {
        $this->markRead($demandId, $partyType, $partyId, $readerRole);

        return Message::where('demand_id', $demandId)
            ->where('thread_party_type', $partyType)
            ->where('thread_party_id', $partyId)
            ->orderBy('created_at')
            ->get();
    }

    public function send(
        int $demandId,
        string $partyType,
        int $partyId,
        string $senderType,
        int $senderId,
        string $senderName,
        string $body,
    ): Message {
        $message = Message::create([
            'demand_id'         => $demandId,
            'sender_type'       => $senderType,
            'sender_id'         => $senderId,
            'thread_party_type' => $partyType,
            'thread_party_id'   => $partyId,
            'body'              => $body,
        ]);

        broadcast(new MessageSent($message, $demandId, $senderName, $partyType));
        $this->pingRecipient($demandId, $partyType, $partyId, $senderType, $senderName, $body);

        return $message;
    }

    public function markRead(int $demandId, string $partyType, int $partyId, string $readerRole): int
    {
        $updated = Message::where('demand_id', $demandId)
            ->where('thread_party_type', $partyType)
            ->where('thread_party_id', $partyId)
            ->where('sender_type', '!=', $readerRole)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            $this->pingReader($demandId, $partyType, $partyId, $readerRole);
        }

        return $updated;
    }

    // ── Inbox / badge ──────────────────────────────────────────────────────────

    public function inboxForProvider(int $providerId): Collection
    {
        [$lastMessage, $unread] = $this->threadAggregates('provider', $providerId);

        return Proposal::query()
            ->where('provider_id', $providerId)
            ->with(['demand:id,title,company_id', 'demand.company:id,trade_name'])
            ->leftJoinSub($lastMessage, 'lm', 'lm.demand_id', '=', 'proposals.demand_id')
            ->leftJoinSub($unread, 'uc', 'uc.demand_id', '=', 'proposals.demand_id')
            ->leftJoin('messages as m', 'm.id', '=', 'lm.last_id')
            ->select(
                'proposals.*',
                'm.body as last_message_body',
                'm.created_at as last_message_at',
                DB::raw('COALESCE(uc.unread_count, 0) as unread_count'),
            )
            ->orderByRaw('COALESCE(m.created_at, proposals.created_at) DESC')
            ->get();
    }

    public function inboxForCompany(int $companyId): Collection
    {
        [$lastMessage, $unread] = $this->threadAggregates('company', $companyId);

        return Demand::query()
            ->where('company_id', $companyId)
            ->whereHas('proposals')
            ->leftJoinSub($lastMessage, 'lm', 'lm.demand_id', '=', 'demands.id')
            ->leftJoinSub($unread, 'uc', 'uc.demand_id', '=', 'demands.id')
            ->leftJoin('messages as m', 'm.id', '=', 'lm.last_id')
            ->select(
                'demands.*',
                'm.body as last_message_body',
                'm.created_at as last_message_at',
                DB::raw('COALESCE(uc.unread_count, 0) as unread_count'),
            )
            ->orderByRaw('COALESCE(m.created_at, demands.created_at) DESC')
            ->get();
    }

    public function totalUnreadForProvider(int $providerId): int
    {
        return (int) Message::where('thread_party_type', 'provider')
            ->where('thread_party_id', $providerId)
            ->where('sender_type', '!=', 'provider')
            ->whereNull('read_at')
            ->count();
    }

    public function totalUnreadForCompany(int $companyId): int
    {
        return (int) Message::where('thread_party_type', 'company')
            ->where('thread_party_id', $companyId)
            ->where('sender_type', '!=', 'company')
            ->whereNull('read_at')
            ->count();
    }

    public function totalUnreadForAdmin(): int
    {
        return (int) Message::whereIn('thread_party_type', ['provider', 'company'])
            ->where('sender_type', '!=', 'admin')
            ->whereNull('read_at')
            ->count();
    }

    private function threadAggregates(string $partyType, int $partyId): array
    {
        $lastMessage = Message::query()
            ->selectRaw('demand_id, MAX(id) as last_id')
            ->where('thread_party_type', $partyType)
            ->where('thread_party_id', $partyId)
            ->groupBy('demand_id');

        $unread = Message::query()
            ->selectRaw('demand_id, COUNT(*) as unread_count')
            ->where('thread_party_type', $partyType)
            ->where('thread_party_id', $partyId)
            ->where('sender_type', '!=', $partyType)
            ->whereNull('read_at')
            ->groupBy('demand_id');

        return [$lastMessage, $unread];
    }

    // ── Ping em tempo real dos canais pessoais (badge) ───────────────────────────

    private function pingRecipient(int $demandId, string $partyType, int $partyId, string $senderType, string $senderName, string $body): void
    {
        if ($senderType === 'admin') {
            $channel = $partyType === 'provider' ? "chat-inbox.provider.{$partyId}" : "chat-inbox.company.{$partyId}";
            $count   = $partyType === 'provider' ? $this->totalUnreadForProvider($partyId) : $this->totalUnreadForCompany($partyId);
        } else {
            $channel = 'chat-inbox.admin';
            $count   = $this->totalUnreadForAdmin();
        }

        broadcast(new ChatInboxPing($channel, $demandId, $partyType, $count, $body, $senderName));
    }

    private function pingReader(int $demandId, string $partyType, int $partyId, string $readerRole): void
    {
        $channel = match ($readerRole) {
            'provider' => "chat-inbox.provider.{$partyId}",
            'company'  => "chat-inbox.company.{$partyId}",
            'admin'    => 'chat-inbox.admin',
            default    => null,
        };

        if (!$channel) {
            return;
        }

        $count = match ($readerRole) {
            'provider' => $this->totalUnreadForProvider($partyId),
            'company'  => $this->totalUnreadForCompany($partyId),
            'admin'    => $this->totalUnreadForAdmin(),
        };

        broadcast(new ChatInboxPing($channel, $demandId, $partyType, $count, null, null));
    }
}
