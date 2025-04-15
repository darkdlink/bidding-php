<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Qualquer usuário autenticado pode ver suas próprias propostas
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Proposal $proposal)
    {
        // Usuário pode ver sua própria proposta ou administradores/gerentes podem ver qualquer proposta
        return $user->id === $proposal->user_id || in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Qualquer usuário autenticado pode criar propostas
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Proposal $proposal)
    {
        // Usuário só pode editar suas próprias propostas
        // E apenas se a proposta estiver em rascunho (draft) e a licitação ainda estiver aberta
        return $user->id === $proposal->user_id &&
               $proposal->status === 'draft' &&
               $proposal->bidding &&
               $proposal->bidding->canSubmitProposal();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Proposal $proposal)
    {
        // Usuário só pode excluir suas próprias propostas em rascunho
        // Administradores podem excluir qualquer proposta
        return ($user->id === $proposal->user_id && $proposal->status === 'draft') ||
                $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Proposal $proposal)
    {
        // Apenas administradores podem restaurar propostas excluídas
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Proposal $proposal)
    {
        // Apenas administradores podem excluir permanentemente propostas
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can submit the proposal.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function submit(User $user, Proposal $proposal)
    {
        // Usuário só pode enviar suas próprias propostas em rascunho
        // E apenas se a licitação ainda estiver aberta
        return $user->id === $proposal->user_id &&
               $proposal->status === 'draft' &&
               $proposal->bidding &&
               $proposal->bidding->canSubmitProposal();
    }

    /**
     * Determine whether the user can mark a proposal as winner.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function markAsWinner(User $user, Proposal $proposal)
    {
        // Apenas administradores e gerentes podem marcar propostas como vencedoras
        // E somente se a proposta estiver enviada (submitted)
        // E a licitação estiver fechada (closed)
        return in_array($user->role, ['admin', 'manager']) &&
               $proposal->status === 'submitted' &&
               $proposal->bidding &&
               $proposal->bidding->status === 'closed';
    }
}
