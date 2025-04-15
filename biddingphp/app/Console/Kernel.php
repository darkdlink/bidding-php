<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ScrapeAvailableBiddings::class,
        Commands\GenerateDailyReports::class,
        Commands\NotifyClosingBiddings::class,
        Commands\CleanupOldLogs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Executa o scraping de licitações todos os dias à 1h da manhã
        $schedule->command('bidding:scrape-all')
                 ->dailyAt('01:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/scraping.log'));

        // Gera relatórios diários às 5h da manhã
        $schedule->command('bidding:generate-daily-reports')
                 ->dailyAt('05:00')
                 ->withoutOverlapping();

        // Notifica sobre licitações que encerram em breve (todos os dias às 8h)
        $schedule->command('bidding:notify-closing')
                 ->dailyAt('08:00');

        // Limpa logs antigos (uma vez por semana)
        $schedule->command('bidding:cleanup-logs')
                 ->weekly()
                 ->mondays()
                 ->at('00:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
