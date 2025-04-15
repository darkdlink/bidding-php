<?php

namespace App\Policies;

use App\Models\ScrapingConfig;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScrapingConfigPolicy
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
        // Apenas administradores podem ver a lista de configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ScrapingConfig $scrapingConfig)
    {
        // Apenas administradores podem ver detalhes de configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Apenas administradores podem criar configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ScrapingConfig $scrapingConfig)
    {
        // Apenas administradores podem editar configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ScrapingConfig $scrapingConfig)
    {
        // Apenas administradores podem excluir configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ScrapingConfig $scrapingConfig)
    {
        // Apenas administradores podem restaurar configurações de scraping excluídas
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ScrapingConfig $scrapingConfig)
    {
        // Apenas administradores podem excluir permanentemente configurações de scraping
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can run scraping manually.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function runScraping(User $user)
    {
        // Apenas administradores e gerentes podem executar scraping manualmente
        return in_array($user->role, ['admin', 'manager']);
    }
}
