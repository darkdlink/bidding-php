<?php

namespace App\Console\Commands;

use App\Models\Bidding;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyClosingBiddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidding:notify-closing
                           {--days=3 : Número de dias até o fechamento para notificação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica usuários sobre licitações que estão próximas do encerramento';

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Verificando licitações que encerram em {$days} dias...");

        // Busca licitações que estão prestes a fechar
        $dateLimit = now()->addDays($days)->endOfDay();
        $biddings = Bidding::active()
                           ->whereDate('closing_date', '<=', $dateLimit)
                           ->whereDate('closing_date', '>=', now())
                           ->get();

        $this->info("Encontradas {$biddings->count()} licitações prestes a encerrar.");

        $notificationCount = 0;

        foreach ($biddings as $bidding) {
            $this->info("Processando notificações para: {$bidding->title}");

            try {
                $this->notificationService->notifyBiddingClosingSoon($bidding);
                $notificationCount++;
                $this->info("Notificações enviadas com sucesso.");
            } catch (\Exception $e) {
                $this->error("Erro ao enviar notificações: " . $e->getMessage());
                Log::error("Erro ao notificar sobre licitação {$bidding->id}: " . $e->getMessage());
            }
        }

        $this->info("Total de {$notificationCount} licitações notificadas.");
        return 0;
    }
}
