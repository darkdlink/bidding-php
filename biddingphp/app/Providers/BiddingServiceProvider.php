<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ScrapingService;
use App\Services\ProposalCalculationService;
use App\Services\NotificationService;
use App\Services\ReportGenerationService;

class BiddingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registro dos serviços como singletons
        $this->app->singleton(ScrapingService::class, function ($app) {
            return new ScrapingService();
        });

        $this->app->singleton(ProposalCalculationService::class, function ($app) {
            return new ProposalCalculationService();
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(ReportGenerationService::class, function ($app) {
            return new ReportGenerationService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configurações específicas do sistema de licitações
        config(['bidding.allowed_types' => [
            'pregão',
            'concorrência',
            'tomada de preços',
            'convite',
            'leilão',
            'concurso',
            'outros'
        ]]);

        config(['bidding.allowed_statuses' => [
            'draft' => 'Rascunho',
            'published' => 'Publicada',
            'in_progress' => 'Em Andamento',
            'closed' => 'Fechada',
            'cancelled' => 'Cancelada',
            'awarded' => 'Adjudicada'
        ]]);
    }
}
