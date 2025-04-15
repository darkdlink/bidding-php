<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Bidding;
use App\Models\Proposal;
use App\Policies\BiddingPolicy;
use App\Policies\ProposalPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Bidding::class => BiddingPolicy::class,
        Proposal::class => ProposalPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define permissões usando Gates
        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('scrape-biddings', function ($user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['admin', 'manager', 'analyst']);
        });

        Gate::define('view-proposals', function ($user, $bidding) {
            return $user->role === 'admin' || $user->role === 'manager';
        });
    }
}
