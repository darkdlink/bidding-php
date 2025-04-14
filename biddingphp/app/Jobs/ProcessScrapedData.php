<?php

namespace App\Jobs;

use App\Models\ScrapingConfig;
use App\Services\ScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScrapedData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Dados básicos da licitação coletada
     */
    protected $biddingData;

    /**
     * ID da configuração de scraping
     */
    protected $configId;

    /**
     * Número de tentativas para executar o job
     */
    public $tries = 3;

    /**
     * Tempo máximo em segundos que o job pode ser executado
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $biddingData, int $configId)
    {
        $this->biddingData = $biddingData;
        $this->configId = $configId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ScrapingService $scrapingService)
    {
        try {
            // Busca a configuração de scraping
            $config = ScrapingConfig::findOrFail($this->configId);

            // Processa os detalhes da licitação
            $bidding = $scrapingService->scrapeDetailedBidding($this->biddingData, $config);

            if ($bidding) {
                Log::info("Licitação processada com sucesso: {$bidding->title} (ID: {$bidding->id})");

                // Dispara notificações
                SendBiddingNotifications::dispatch($bidding, 'new');
            } else {
                Log::error("Falha ao processar detalhes da licitação: " . json_encode($this->biddingData));
            }
        } catch (\Exception $e) {
            Log::error("Erro ao processar dados da licitação: " . $e->getMessage());

            // Retentar após falha
            if ($this->attempts() < $this->tries) {
                $this->release(60 * $this->attempts());
            }

            throw $e;
        }
    }

    /**
     * Manipula falha do job
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job ProcessScrapedData falhou: " . $exception->getMessage());

        // Aqui pode-se adicionar notificação para administradores ou outras ações
    }
}
