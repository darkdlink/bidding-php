<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\Proposal;
use App\Models\BiddingAgency;
use App\Models\BiddingAnalytic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe o dashboard principal
     */
    public function index()
    {
        // Licitações ativas
        $activeBiddings = Bidding::active()->count();

        // Licitações fechando em breve (próximos 7 dias)
        $closingSoonBiddings = Bidding::closingSoon(7)->count();

        // Propostas do usuário por status
        $userProposalsByStatus = Proposal::where('user_id', Auth::id())
                                        ->select('status', DB::raw('count(*) as total'))
                                        ->groupBy('status')
                                        ->pluck('total', 'status')
                                        ->toArray();

        // Valor total de propostas ganhas
        $totalWonValue = Proposal::where('user_id', Auth::id())
                                 ->where('status', 'won')
                                 ->sum('total_value');

        // Taxa de sucesso das propostas enviadas
        $submittedProposals = Proposal::where('user_id', Auth::id())
                                      ->whereIn('status', ['submitted', 'won', 'lost'])
                                      ->count();

        $wonProposals = Proposal::where('user_id', Auth::id())
                                ->where('status', 'won')
                                ->count();

        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

        // Licitações por tipo
        $biddingsByType = Bidding::select('bidding_type', DB::raw('count(*) as total'))
                                 ->groupBy('bidding_type')
                                 ->pluck('total', 'bidding_type')
                                 ->toArray();

        // Licitações por órgão (top 5)
        $biddingsByAgency = Bidding::select('agency_id', DB::raw('count(*) as total'))
                                   ->with('agency')
                                   ->groupBy('agency_id')
                                   ->orderBy('total', 'desc')
                                   ->limit(5)
                                   ->get()
                                   ->map(function ($item) {
                                       return [
                                           'name' => $item->agency->name ?? 'Desconhecido',
                                           'total' => $item->total,
                                       ];
                                   });

        // Dados históricos para gráficos
        $lastSixMonths = $this->getLastMonths(6);

        // Licitações por mês (últimos 6 meses)
        $biddingsByMonth = Bidding::select(
                                    DB::raw('DATE_FORMAT(publication_date, "%Y-%m") as month'),
                                    DB::raw('count(*) as total')
                                )
                                ->whereNotNull('publication_date')
                                ->whereDate('publication_date', '>=', $lastSixMonths[5]['date'])
                                ->groupBy('month')
                                ->pluck('total', 'month')
                                ->toArray();

        // Formata os dados para o gráfico
        $biddingsChartData = [];
        foreach ($lastSixMonths as $month) {
            $key = $month['key'];
            $biddingsChartData[] = [
                'month' => $month['label'],
                'total' => $biddingsByMonth[$key] ?? 0,
            ];
        }

        // Propostas por mês (últimos 6 meses)
        $proposalsByMonth = Proposal::select(
                                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                                    DB::raw('count(*) as total')
                                )
                                ->where('user_id', Auth::id())
                                ->whereDate('created_at', '>=', $lastSixMonths[5]['date'])
                                ->groupBy('month')
                                ->pluck('total', 'month')
                                ->toArray();

        // Propostas ganhas por mês (últimos 6 meses)
        $wonProposalsByMonth = Proposal::select(
                                    DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as month'),
                                    DB::raw('count(*) as total')
                                )
                                ->where('user_id', Auth::id())
                                ->where('status', 'won')
                                ->whereDate('updated_at', '>=', $lastSixMonths[5]['date'])
                                ->groupBy('month')
                                ->pluck('total', 'month')
                                ->toArray();

        // Formata os dados para o gráfico
        $proposalsChartData = [];
        foreach ($lastSixMonths as $month) {
            $key = $month['key'];
            $proposalsChartData[] = [
                'month' => $month['label'],
                'total' => $proposalsByMonth[$key] ?? 0,
                'won' => $wonProposalsByMonth[$key] ?? 0,
            ];
        }

        // Licitações recentes
        $recentBiddings = Bidding::with('agency')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

        // Propostas recentes do usuário
        $recentProposals = Proposal::with(['bidding', 'bidding.agency'])
                                   ->where('user_id', Auth::id())
                                   ->orderBy('created_at', 'desc')
                                   ->limit(5)
                                   ->get();

        return view('dashboard.index', compact(
            'activeBiddings',
            'closingSoonBiddings',
            'userProposalsByStatus',
            'totalWonValue',
            'successRate',
            'biddingsByType',
            'biddingsByAgency',
            'biddingsChartData',
            'proposalsChartData',
            'recentBiddings',
            'recentProposals'
        ));
    }

    /**
     * Exibe relatórios avançados
     */
    public function reports(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Total de licitações no período
        $totalBiddings = Bidding::whereBetween('publication_date', [$start, $end])->count();

        // Total de propostas enviadas no período
        $totalProposals = Proposal::where('user_id', Auth::id())
                                  ->whereIn('status', ['submitted', 'won', 'lost'])
                                  ->whereBetween('submission_date', [$start, $end])
                                  ->count();

        // Total de propostas ganhas no período
        $totalWonProposals = Proposal::where('user_id', Auth::id())
                                    ->where('status', 'won')
                                    ->whereBetween('updated_at', [$start, $end])
                                    ->count();

        // Valor total das propostas ganhas no período
        $totalWonValue = Proposal::where('user_id', Auth::id())
                                 ->where('status', 'won')
                                 ->whereBetween('updated_at', [$start, $end])
                                 ->sum('total_value');

        // Taxa de sucesso no período
        $successRate = $totalProposals > 0 ? ($totalWonProposals / $totalProposals) * 100 : 0;

        // Licitações por órgão no período
        $biddingsByAgency = Bidding::select('agency_id', DB::raw('count(*) as total'))
                                   ->with('agency')
                                   ->whereBetween('publication_date', [$start, $end])
                                   ->groupBy('agency_id')
                                   ->orderBy('total', 'desc')
                                   ->get();

        // Propostas por órgão no período
        $proposalsByAgency = Proposal::select('bidding_id', DB::raw('count(*) as total'))
                                    ->where('user_id', Auth::id())
                                    ->whereIn('status', ['submitted', 'won', 'lost'])
                                    ->whereBetween('submission_date', [$start, $end])
                                    ->with(['bidding', 'bidding.agency'])
                                    ->get()
                                    ->groupBy(function ($item) {
                                        return $item->bidding->agency->name ?? 'Desconhecido';
                                    })
                                    ->map(function ($items) {
                                        return $items->sum('total');
                                    });

        // Propostas ganhas por órgão no período
        $wonProposalsByAgency = Proposal::select('bidding_id', DB::raw('count(*) as total'))
                                       ->where('user_id', Auth::id())
                                       ->where('status', 'won')
                                       ->whereBetween('updated_at', [$start, $end])
                                       ->with(['bidding', 'bidding.agency'])
                                       ->get()
                                       ->groupBy(function ($item) {
                                           return $item->bidding->agency->name ?? 'Desconhecido';
                                       })
                                       ->map(function ($items) {
                                           return $items->sum('total');
                                       });

        // Licitações por tipo no período
        $biddingsByType = Bidding::select('bidding_type', DB::raw('count(*) as total'))
                                 ->whereBetween('publication_date', [$start, $end])
                                 ->groupBy('bidding_type')
                                 ->pluck('total', 'bidding_type')
                                 ->toArray();

        // Evolução mensal no período
        $months = $this->getMonthsBetweenDates($start, $end);

        // Licitações por mês no período
        $biddingsByMonth = Bidding::select(
                                DB::raw('DATE_FORMAT(publication_date, "%Y-%m") as month'),
                                DB::raw('count(*) as total')
                            )
                            ->whereNotNull('publication_date')
                            ->whereBetween('publication_date', [$start, $end])
                            ->groupBy('month')
                            ->pluck('total', 'month')
                            ->toArray();

        // Propostas por mês no período
        $proposalsByMonth = Proposal::select(
                                DB::raw('DATE_FORMAT(submission_date, "%Y-%m") as month'),
                                DB::raw('count(*) as total')
                            )
                            ->where('user_id', Auth::id())
                            ->whereIn('status', ['submitted', 'won', 'lost'])
                            ->whereNotNull('submission_date')
                            ->whereBetween('submission_date', [$start, $end])
                            ->groupBy('month')
                            ->pluck('total', 'month')
                            ->toArray();

        // Valor ganho por mês no período
        $valueByMonth = Proposal::select(
                            DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as month'),
                            DB::raw('SUM(total_value) as total')
                        )
                        ->where('user_id', Auth::id())
                        ->where('status', 'won')
                        ->whereBetween('updated_at', [$start, $end])
                        ->groupBy('month')
                        ->pluck('total', 'month')
                        ->toArray();

        // Formata os dados para o gráfico
        $monthlyChartData = [];
        foreach ($months as $month) {
            $key = $month['key'];
            $monthlyChartData[] = [
                'month' => $month['label'],
                'biddings' => $biddingsByMonth[$key] ?? 0,
                'proposals' => $proposalsByMonth[$key] ?? 0,
                'value' => $valueByMonth[$key] ?? 0,
            ];
        }

        // Lista de órgãos para filtro
        $agencies = BiddingAgency::orderBy('name')->get();

        return view('dashboard.reports', compact(
            'startDate',
            'endDate',
            'totalBiddings',
            'totalProposals',
            'totalWonProposals',
            'totalWonValue',
            'successRate',
            'biddingsByAgency',
            'proposalsByAgency',
            'wonProposalsByAgency',
            'biddingsByType',
            'monthlyChartData',
            'agencies'
        ));
    }

    /**
     * Retorna um array com os últimos N meses
     */
    private function getLastMonths($count = 6)
    {
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$count - $i - 1] = [
                'date' => $date->copy()->startOfMonth(),
                'key' => $date->format('Y-m'),
                'label' => $date->format('M/Y'),
            ];
        }
        return $months;
    }

    /**
     * Retorna um array com os meses entre duas datas
     */
    private function getMonthsBetweenDates($startDate, $endDate)
    {
        $months = [];
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        do {
            $months[] = [
                'date' => $start->copy(),
                'key' => $start->format('Y-m'),
                'label' => $start->format('M/Y'),
            ];

            $start->addMonth();
        } while ($start->lte($end));

        return $months;
    }
}
