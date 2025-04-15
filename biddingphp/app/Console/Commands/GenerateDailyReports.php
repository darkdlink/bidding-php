<?php

namespace App\Console\Commands;

use App\Services\ReportGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidding:generate-daily-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera relatórios diários de licitações e propostas';

    /**
     * The report generation service instance.
     *
     * @var \App\Services\ReportGenerationService
     */
    protected $reportService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\ReportGenerationService  $reportService
     * @return void
     */
    public function __construct(ReportGenerationService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando geração de relatórios diários...');

        try {
            // Gera estatísticas diárias
            $analytics = $this->reportService->generateDailyAnalytics();
            $this->info("Estatísticas diárias geradas para: {$analytics->reference_date}");

            // Obtém dados sobre as estatísticas
            $this->info("- Licitações ativas: {$analytics->total_active_biddings}");
            $this->info("- Propostas enviadas: {$analytics->total_submitted_proposals}");
            $this->info("- Propostas vencedoras: {$analytics->total_won_proposals}");
            $this->info("- Valor total ganho: R$ " . number_format($analytics->total_value_won, 2, ',', '.'));
            $this->info("- Taxa de sucesso: {$analytics->success_rate}%");

            $this->info('Relatórios diários gerados com sucesso.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar relatórios diários: ' . $e->getMessage());
            Log::error('Erro ao gerar relatórios diários: ' . $e->getMessage());
            return 1;
        }
    }
}
