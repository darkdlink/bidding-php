<?php

namespace App\Services;

use App\Models\Bidding;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\BiddingNotification;
use App\Mail\ProposalNotification;
use App\Jobs\SendBiddingNotifications;

class NotificationService
{
    /**
     * Notifica sobre uma nova licitação
     */
    public function notifyNewBidding(Bidding $bidding)
    {
        // Cria a notificação para usuários interessados
        $interestedUsers = $this->getInterestedUsers($bidding);

        foreach ($interestedUsers as $user) {
            $notification = new Notification([
                'user_id' => $user->id,
                'title' => 'Nova Licitação',
                'message' => "Uma nova licitação foi cadastrada: {$bidding->title}",
                'type' => 'bidding',
                'related_type' => 'bidding',
                'related_id' => $bidding->id,
            ]);

            $notification->save();

            // Enfileira o job para enviar e-mail
            SendBiddingNotifications::dispatch($user, $bidding, 'new');
        }
    }

    /**
     * Notifica sobre uma atualização em uma licitação
     */
    public function notifyBiddingUpdated(Bidding $bidding)
    {
        // Busca usuários que possuem proposta para esta licitação
        $usersWithProposals = User::whereHas('proposals', function ($query) use ($bidding) {
            $query->where('bidding_id', $bidding->id);
        })->get();

        foreach ($usersWithProposals as $user) {
            $notification = new Notification([
                'user_id' => $user->id,
                'title' => 'Licitação Atualizada',
                'message' => "A licitação {$bidding->title} foi atualizada.",
                'type' => 'bidding_update',
                'related_type' => 'bidding',
                'related_id' => $bidding->id,
            ]);

            $notification->save();

            // Enfileira o job para enviar e-mail
            SendBiddingNotifications::dispatch($user, $bidding, 'update');
        }
    }

    /**
     * Notifica sobre o encerramento próximo de uma licitação
     */
    public function notifyBiddingClosingSoon(Bidding $bidding)
    {
        // Notifica todos usuários com propostas em rascunho
        $usersWithDrafts = User::whereHas('proposals', function ($query) use ($bidding) {
            $query->where('bidding_id', $bidding->id)
                  ->where('status', 'draft');
        })->get();

        foreach ($usersWithDrafts as $user) {
            $notification = new Notification([
                'user_id' => $user->id,
                'title' => 'Licitação Encerrando em Breve',
                'message' => "A licitação {$bidding->title} encerra em {$bidding->daysUntilClosing()} dias.",
                'type' => 'bidding_closing',
                'related_type' => 'bidding',
                'related_id' => $bidding->id,
            ]);

            $notification->save();

            // Enfileira o job para enviar e-mail
            SendBiddingNotifications::dispatch($user, $bidding, 'closing');
        }
    }

    /**
     * Notifica sobre o resultado de uma licitação
     */
    public function notifyBiddingResult(Bidding $bidding, $winningProposalId = null)
    {
        // Busca todos os usuários que enviaram propostas
        $usersWithProposals = User::whereHas('proposals', function ($query) use ($bidding) {
            $query->where('bidding_id', $bidding->id)
                  ->whereIn('status', ['submitted', 'won', 'lost']);
        })->get();

        foreach ($usersWithProposals as $user) {
            $userProposal = Proposal::where('bidding_id', $bidding->id)
                                   ->where('user_id', $user->id)
                                   ->whereIn('status', ['submitted', 'won', 'lost'])
                                   ->first();

            if ($userProposal) {
                $isWinner = $winningProposalId && $userProposal->id == $winningProposalId;

                $notification = new Notification([
                    'user_id' => $user->id,
                    'title' => 'Resultado da Licitação',
                    'message' => $isWinner
                               ? "Parabéns! Sua proposta para {$bidding->title} foi vencedora."
                               : "A licitação {$bidding->title} foi encerrada.",
                    'type' => 'bidding_result',
                    'related_type' => 'bidding',
                    'related_id' => $bidding->id,
                ]);

                $notification->save();

                // Atualiza o status da proposta
                if ($winningProposalId) {
                    $userProposal->status = $isWinner ? 'won' : 'lost';
                    $userProposal->save();
                }

                // Enfileira o job para enviar e-mail
                SendBiddingNotifications::dispatch($user, $bidding, $isWinner ? 'won' : 'lost');
            }
        }
    }

    /**
     * Notifica sobre o envio de uma proposta
     */
    public function notifyProposalSubmitted(Proposal $proposal)
    {
        $user = $proposal->user;
        $bidding = $proposal->bidding;

        $notification = new Notification([
            'user_id' => $user->id,
            'title' => 'Proposta Enviada',
            'message' => "Sua proposta para {$bidding->title} foi enviada com sucesso.",
            'type' => 'proposal_submitted',
            'related_type' => 'proposal',
            'related_id' => $proposal->id,
        ]);

        $notification->save();

        // Enviar e-mail
        try {
            Mail::to($user->email)->queue(new ProposalNotification(
                $user,
                $proposal,
                'Proposta Enviada com Sucesso',
                "Sua proposta para a licitação {$bidding->title} foi enviada com sucesso."
            ));
        } catch (\Exception $e) {
            // Log do erro de envio
            \Log::error("Erro ao enviar e-mail de notificação: " . $e->getMessage());
        }
    }

    /**
     * Marca uma notificação como lida
     */
    public function markAsRead($notificationId, $userId)
    {
        return Notification::where('id', $notificationId)
                          ->where('user_id', $userId)
                          ->update(['read' => true]);
    }

    /**
     * Marca todas as notificações de um usuário como lidas
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
                          ->update(['read' => true]);
    }

    /**
     * Obtém as notificações não lidas de um usuário
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
                          ->where('read', false)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Obtém o total de notificações não lidas de um usuário
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
                          ->where('read', false)
                          ->count();
    }

    /**
     * Obtém usuários interessados em uma licitação com base em preferências ou histórico
     * Esta é uma versão simplificada - na prática seria baseada em critérios mais complexos
     */
    private function getInterestedUsers(Bidding $bidding)
    {
        // Por exemplo, usuários com propostas para licitações similares
        $similarBiddings = Bidding::where('bidding_type', $bidding->bidding_type)
                                  ->where('id', '!=', $bidding->id)
                                  ->pluck('id');

        $interestedUsers = User::whereHas('proposals', function ($query) use ($similarBiddings) {
            $query->whereIn('bidding_id', $similarBiddings);
        })->get();

        // Se não houver usuários interessados baseados no histórico, retorna todos os usuários
        if ($interestedUsers->count() == 0) {
            // Em um sistema real, filtraria por preferências ou perfil
            $interestedUsers = User::all();
        }

        return $interestedUsers;
    }
}
