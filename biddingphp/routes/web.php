<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BiddingController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BiddingAgencyController;
use App\Http\Controllers\ScrapingConfigController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;

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
Auth::routes(['verify' => true]);

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reports', [DashboardController::class, 'reports'])->name('dashboard.reports');

    // Perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');

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
    Route::post('proposals/compare', [ProposalController::class, 'compare'])->name('proposals.compare');
    Route::get('proposals/{proposal}/pdf', [ProposalController::class, 'generatePdf'])->name('proposals.pdf');
    Route::get('proposals/{proposal}/excel', [ProposalController::class, 'exportExcel'])->name('proposals.excel');

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
    Route::put('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

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
    Route::get('scraping-logs/{log}', [AdminController::class, 'viewScrapingLog'])->name('scraping-logs.view');
    Route::delete('scraping-logs/{log}', [AdminController::class, 'deleteScrapingLog'])->name('scraping-logs.delete');
    Route::post('scraping-configs/{config}/run', [ScrapingConfigController::class, 'runManually'])
        ->name('scraping-configs.run');
});

// Rotas para webhooks e integração
Route::prefix('webhooks')->group(function () {
    Route::post('bidding-update', [BiddingController::class, 'webhookUpdate'])
        ->middleware('api-key')
        ->name('webhooks.bidding.update');
});

// Rotas para verificação de e-mail
Route::middleware(['auth', 'signed'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->name('verification.verify');

    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.resend');
});
