<?php

namespace App\Policies;

use App\Models\BiddingAgency;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgencyPolicy
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
        // Qualquer usuário autenticado pode ver a lista de órgãos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, BiddingAgency $agency)
    {
        // Qualquer usuário autenticado pode ver detalhes de um órgão
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
        // Apenas administradores e gerentes podem criar órgãos
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, BiddingAgency $agency)
    {
        // Apenas administradores e gerentes podem editar órgãos
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, BiddingAgency $agency)
    {
        // Apenas administradores podem excluir órgãos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, BiddingAgency $agency)
    {
        // Apenas administradores podem restaurar órgãos excluídos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, BiddingAgency $agency)
    {
        // Apenas administradores podem excluir permanentemente órgãos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can configure scraping for the agency.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function configureScraping(User $user, BiddingAgency $agency)
    {
        // Apenas administradores podem configurar scraping para órgãos
        return $user->role === 'admin';
    }
}
