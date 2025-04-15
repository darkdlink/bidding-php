<?php

namespace App\Console\Commands;

use App\Models\ScrapingConfig;
use App\Services\ScrapingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeAvailableBiddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidding:scrape-all
                            {--agency_id= : ID do órgão específico para realizar o scraping}
                            {--force : Força a execução mesmo para configurações inativas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza o scraping de todas as fontes de licitações configuradas';

    /**
     * The scraping service instance.
     *
     * @var \App\Services\ScrapingService
     */
    protected $scrapingService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\ScrapingService  $scrapingService
     * @return void
     */
    public function __construct(ScrapingService $scrapingService)
    {
        parent::__construct();
        $this->scrapingService = $scrapingService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando o processo de scraping de licitações...');

        // Obtém as configurações de scraping
        $query = ScrapingConfig::query()->with('agency');

        // Filtra por órgão específico se fornecido
        if ($agencyId = $this->option('agency_id')) {
            $query->where('agency_id', $agencyId);
            $this->info("Filtrando apenas para o órgão ID: {$agencyId}");
        }

        // Considera apenas configurações ativas, a menos que a flag --force seja usada
        if (!$this->option('force')) {
            $query->where('active', true);
            $this->info("Processando apenas configurações ativas. Use --force para processar todas.");
        }

        $configs = $query->get();

        $this->info("Encontradas {$configs->count()} configuração(ões) de scraping.");

        $successCount = 0;
        $failCount = 0;

        // Processa cada configuração
        foreach ($configs as $config) {
            $this->info("Processando scraping para: {$config->agency->name}");

            try {
                $startTime = microtime(true);

                $result = $this->scrapingService->scrapeAgencyBiddings($config);

                $duration = round(microtime(true) - $startTime, 2);

                if ($result) {
                    $this->info("Scraping concluído com sucesso em {$duration} segundos.");
                    $successCount++;
                } else {
                    $this->error("Falha no scraping após {$duration} segundos.");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->error("Erro ao processar scraping: " . $e->getMessage());
                Log::error("Erro no scraping para {$config->agency->name}: " . $e->getMessage());
                $failCount++;
            }

            // Aguarda um pouco antes da próxima requisição para não sobrecarregar o servidor
            if ($configs->count() > 1) {
                $this->info("Aguardando 3 segundos antes da próxima execução...");
                sleep(3);
            }
        }

        $this->info("Scraping finalizado. Sucessos: {$successCount}, Falhas: {$failCount}");

        return 0;
    }
}
