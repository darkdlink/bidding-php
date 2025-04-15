<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidding;
use App\Models\BiddingAgency;
use App\Models\Proposal;
use App\Models\ScrapingConfig;
use App\Models\ScrapingLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware(['auth', 'can:access-admin']);
    }

    /**
     * Painel administrativo
     */
    public function index()
    {
        // Estatísticas gerais
        $totalUsers = User::count();
        $totalBiddings = Bidding::count();
        $totalProposals = Proposal::count();
        $totalAgencies = BiddingAgency::count();

        // Licitações recentes
        $recentBiddings = Bidding::with('agency')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(10)
                                 ->get();

        // Propostas recentes
        $recentProposals = Proposal::with(['user', 'bidding', 'bidding.agency'])
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

        // Logs de scraping recentes
        $recentScrapingLogs = ScrapingLog::with('config.agency')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();

        return view('admin.index', compact(
            'totalUsers',
            'totalBiddings',
            'totalProposals',
            'totalAgencies',
            'recentBiddings',
            'recentProposals',
            'recentScrapingLogs'
        ));
    }

    /**
     * Lista de propostas (visão administrativa)
     */
    public function proposals(Request $request)
    {
        $query = Proposal::with(['user', 'bidding', 'bidding.agency']);

        // Filtros
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('bidding_id') && !empty($request->bidding_id)) {
            $query->where('bidding_id', $request->bidding_id);
        }

        // Ordenação
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $proposals = $query->paginate(15);

        // Dados para filtros
        $users = User::orderBy('name')->get();
        $biddings = Bidding::orderBy('title')->get();

        return view('admin.proposals.index', compact('proposals', 'users', 'biddings'));
    }

    /**
     * Detalhes de uma proposta (visão administrativa)
     */
    public function showProposal(Proposal $proposal)
    {
        $proposal->load(['user', 'bidding', 'bidding.agency', 'items', 'items.bidding_item', 'attachments']);

        return view('admin.proposals.show', compact('proposal'));
    }

    /**
     * Marca uma proposta como vencedora
     */
    public function markProposalAsWinner(Proposal $proposal)
    {
        try {
            DB::beginTransaction();

            // Verifica se a licitação está fechada
            if ($proposal->bidding->status != 'closed') {
                return back()->with('error', 'Apenas licitações fechadas podem ter vencedores definidos.');
            }

            // Atualiza o status da licitação
            $proposal->bidding->status = 'awarded';
            $proposal->bidding->save();

            // Atualiza o status de todas as propostas desta licitação
            Proposal::where('bidding_id', $proposal->bidding_id)
                   ->where('id', '<>', $proposal->id)
                   ->update(['status' => 'lost']);

            // Atualiza o status da proposta vencedora
            $proposal->status = 'won';
            $proposal->save();

            // Notifica os participantes sobre o resultado
            $this->notificationService->notifyBiddingResult($proposal->bidding, $proposal->id);

            DB::commit();

            return back()->with('success', 'Proposta marcada como vencedora com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao marcar proposta como vencedora: ' . $e->getMessage());
        }
    }

    /**
     * Configurações do sistema
     */
    public function settings()
    {
        // Obter configurações atuais
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Atualiza as configurações do sistema
     */
    public function updateSettings(Request $request)
    {
        // ... código para atualizar configurações
    }

    /**
     * Exibe logs de scraping
     */
    public function scrapingLogs(Request $request)
    {
        // ... código para exibir logs de scraping
    }
}
