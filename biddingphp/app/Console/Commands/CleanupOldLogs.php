<?php

namespace App\Console\Commands;

use App\Models\ScrapingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidding:cleanup-logs
                           {--days=30 : Número de dias para manter os logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove logs antigos do sistema para otimizar espaço';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Limpando logs mais antigos que {$days} dias...");

        try {
            // Data limite para remoção
            $dateLimit = now()->subDays($days);

            // Remove logs de scraping antigos
            $count = ScrapingLog::where('created_at', '<', $dateLimit)->delete();
            $this->info("Removidos {$count} logs de scraping.");

            // Remove outros logs do sistema se necessário
            // ...

            $this->info('Limpeza de logs concluída com sucesso.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao limpar logs: ' . $e->getMessage());
            Log::error('Erro ao limpar logs: ' . $e->getMessage());
            return 1;
        }
    }
}
