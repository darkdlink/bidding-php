<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BiddingController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BiddingAgencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar rotas da web para sua aplicação.
| Estas rotas são carregadas pelo RouteServiceProvider dentro de um grupo que
| contém o middleware "web". Crie algo incrível!
|
*/

// Rota inicial - Redireciona para o login ou dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rotas de autenticação (login, registro, reset de senha)
Auth::routes();

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reports', [DashboardController::class, 'reports'])->name('dashboard.reports');

    // Licitações
    Route::resource('biddings', BiddingController::class);
    Route::get('biddings/{bidding}/attachments/{attachment}/download', [BiddingController::class, 'downloadAttachment'])
        ->name('biddings.attachments.download');

    // Scraping de Licitações (apenas para admin/manager)
    Route::middleware(['can:scrape-biddings'])->group(function () {
        Route::get('biddings-scrape', [BiddingController::class, 'showScrapeForm'])->name('biddings.scrape.form');
        Route::post('biddings-scrape', [BiddingController::class, 'scrape'])->name('biddings.scrape');
    });

    // Propostas
    Route::resource('proposals', ProposalController::class)->except(['create']);
    Route::get('proposals/create/{bidding}', [ProposalController::class, 'create'])->name('proposals.create');
    Route::put('proposals/{proposal}/submit', [ProposalController::class, 'submit'])->name('proposals.submit');
    Route::put('proposals/{proposal}/cancel', [ProposalController::class, 'cancel'])->name('proposals.cancel');
    Route::get('proposals/{proposal}/duplicate', [ProposalController::class, 'duplicate'])->name('proposals.duplicate');
    Route::get('proposals/{proposal}/attachments/{attachment}/download', [ProposalController::class, 'downloadAttachment'])
        ->name('proposals.attachments.download');

    // Relatórios
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/agency/{agency}', [ReportController::class, 'agency'])->name('reports.agency');
    Route::get('reports/performance', [ReportController::class, 'performance'])->name('reports.performance');
    Route::get('reports/download/{file}', [ReportController::class, 'download'])->name('reports.download');

    // Notificações
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // API de notificações (para atualizações em tempo real)
    Route::get('api/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::get('api/notifications/recent', [NotificationController::class, 'getRecent']);
});

// Rotas para Administração (apenas admin)
Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Gerenciamento de Usuários
    Route::resource('users', UserController::class);

    // Gerenciamento de Órgãos Licitantes
    Route::resource('agencies', BiddingAgencyController::class);

    // Gerenciamento de Propostas
    Route::get('proposals', [AdminController::class, 'proposals'])->name('proposals.index');
    Route::get('proposals/{proposal}', [AdminController::class, 'showProposal'])->name('proposals.show');
    Route::put('proposals/{proposal}/mark-as-winner', [AdminController::class, 'markProposalAsWinner'])
        ->name('proposals.mark-as-winner');

    // Configurações do Sistema
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Gerenciamento de Scraping
    Route::resource('scraping-configs', ScrapingConfigController::class);
    Route::get('scraping-logs', [AdminController::class, 'scrapingLogs'])->name('scraping-logs');
});
