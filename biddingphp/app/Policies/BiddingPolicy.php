<?php

namespace App\Policies;

use App\Models\Bidding;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BiddingPolicy
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
        // Qualquer usuário autenticado pode ver a lista de licitações
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Bidding $bidding)
    {
        // Qualquer usuário autenticado pode ver detalhes de uma licitação
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Apenas administradores e gerentes podem criar licitações
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Bidding $bidding)
    {
        // Apenas administradores e gerentes podem editar licitações
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Bidding $bidding)
    {
        // Apenas administradores podem excluir licitações
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Bidding $bidding)
    {
        // Apenas administradores podem restaurar licitações excluídas
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Bidding $bidding)
    {
        // Apenas administradores podem excluir permanentemente licitações
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view proposals for the bidding.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewProposals(User $user, Bidding $bidding)
    {
        // Apenas administradores e gerentes podem ver propostas de outras pessoas
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can mark a bidding as awarded.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bidding  $bidding
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function award(User $user, Bidding $bidding)
    {
        // Apenas administradores e gerentes podem adjudicar licitações
        // E somente se a licitação estiver fechada (closed)
        return in_array($user->role, ['admin', 'manager']) && $bidding->status === 'closed';
    }
}
