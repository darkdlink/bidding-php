<?php

namespace App\Services;

use App\Models\Bidding;
use App\Models\BiddingAnalytic;
use App\Models\Proposal;
use App\Models\BiddingAgency;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ReportGenerationService
{
    /**
     * Gera análise diária e salva no banco
     */
    public function generateDailyAnalytics()
    {
        $today = Carbon::today();

        // Verifica se já existe análise para hoje
        $existingAnalytic = BiddingAnalytic::whereDate('reference_date', $today)->first();

        if ($existingAnalytic) {
            return $this->updateDailyAnalytics($existingAnalytic);
        }

        // Coleta métricas
        $totalActiveBiddings = Bidding::active()->count();

        $totalSubmittedProposals = Proposal::whereIn('status', ['submitted', 'won', 'lost'])->count();

        $totalWonProposals = Proposal::where('status', 'won')->count();

        $totalValueWon = Proposal::where('status', 'won')->sum('total_value');

        $successRate = $totalSubmittedProposals > 0
                     ? ($totalWonProposals / $totalSubmittedProposals) * 100
                     : 0;

        // Cria o registro
        return BiddingAnalytic::create([
            'reference_date' => $today,
            'total_active_biddings' => $totalActiveBiddings,
            'total_submitted_proposals' => $totalSubmittedProposals,
            'total_won_proposals' => $totalWonProposals,
            'total_value_won' => $totalValueWon,
            'success_rate' => $successRate,
        ]);
    }

    /**
     * Atualiza análise existente
     */
    private function updateDailyAnalytics(BiddingAnalytic $analytic)
    {
        // Coleta métricas
        $totalActiveBiddings = Bidding::active()->count();

        $totalSubmittedProposals = Proposal::whereIn('status', ['submitted', 'won', 'lost'])->count();

        $totalWonProposals = Proposal::where('status', 'won')->count();

        $totalValueWon = Proposal::where('status', 'won')->sum('total_value');

        $successRate = $totalSubmittedProposals > 0
                     ? ($totalWonProposals / $totalSubmittedProposals) * 100
                     : 0;

        // Atualiza o registro
        $analytic->update([
            'total_active_biddings' => $totalActiveBiddings,
            'total_submitted_proposals' => $totalSubmittedProposals,
            'total_won_proposals' => $totalWonProposals,
            'total_value_won' => $totalValueWon,
            'success_rate' => $successRate,
        ]);

        return $analytic;
    }

    /**
     * Gera relatório mensal de desempenho
     */
    public function generateMonthlyPerformanceReport($month = null, $year = null)
    {
        // Se não informados, usa o mês anterior
        if (is_null($month) || is_null($year)) {
            $date = Carbon::now()->subMonth();
            $month = $date->month;
            $year = $date->year;
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Dados para o relatório
        $data = [
            'period' => $startDate->format('F Y'),
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'metrics' => $this->getMonthlyMetrics($startDate, $endDate),
            'biddings' => $this->getMonthlyBiddings($startDate, $endDate),
            'proposals' => $this->getMonthlyProposals($startDate, $endDate),
            'agencies' => $this->getAgencyPerformance($startDate, $endDate),
            'comparison' => $this->getMonthlyComparison($startDate),
        ];

        return $this->generateExcelReport($data, "relatorio_mensal_{$year}_{$month}.xlsx");
    }

    /**
     * Gera relatório de bidding por agência
     */
    public function generateAgencyReport(BiddingAgency $agency, $startDate = null, $endDate = null)
    {
        // Define período se não informado
        if (is_null($startDate)) {
            $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        } else {
            $startDate = Carbon::parse($startDate)->startOfDay();
        }

        if (is_null($endDate)) {
            $endDate = Carbon::now()->endOfDay();
        } else {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Dados para o relatório
        $data = [
            'agency' => $agency->toArray(),
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'metrics' => $this->getAgencyMetrics($agency, $startDate, $endDate),
            'biddings' => $this->getAgencyBiddings($agency, $startDate, $endDate),
            'proposals' => $this->getAgencyProposals($agency, $startDate, $endDate),
            'monthly_stats' => $this->getAgencyMonthlyStats($agency, $startDate, $endDate),
        ];

        $fileName = "relatorio_agencia_{$agency->id}_{$startDate->format('Y_m_d')}.xlsx";
        return $this->generateExcelReport($data, $fileName);
    }

    /**
     * Gera relatório de propostas para um usuário
     */
    public function generateUserProposalsReport($userId, $startDate = null, $endDate = null)
    {
        // Define período se não informado
        if (is_null($startDate)) {
            $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        } else {
            $startDate = Carbon::parse($startDate)->startOfDay();
        }

        if (is_null($endDate)) {
            $endDate = Carbon::now()->endOfDay();
        } else {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Consultas para o relatório
        $proposals = Proposal::with(['bidding', 'bidding.agency'])
                           ->where('user_id', $userId)
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->orderBy('created_at', 'desc')
                           ->get();

        $submittedProposals = $proposals->whereIn('status', ['submitted', 'won', 'lost'])->count();
        $wonProposals = $proposals->where('status', 'won')->count();
        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;
        $totalValue = $proposals->where('status', 'won')->sum('total_value');

        $statusCounts = $proposals->groupBy('status')
                                ->map(function ($items) {
                                    return $items->count();
                                })
                                ->toArray();

        $agencyCounts = $proposals->groupBy(function ($item) {
                                      return $item->bidding->agency->name ?? 'Desconhecido';
                                  })
                                  ->map(function ($items) {
                                      return $items->count();
                                  })
                                  ->toArray();

        $monthlyStats = $proposals->groupBy(function ($item) {
                                      return $item->created_at->format('Y-m');
                                  })
                                  ->map(function ($items) {
                                      return [
                                          'total' => $items->count(),
                                          'submitted' => $items->whereIn('status', ['submitted', 'won', 'lost'])->count(),
                                          'won' => $items->where('status', 'won')->count(),
                                          'value' => $items->where('status', 'won')->sum('total_value'),
                                      ];
                                  })
                                  ->toArray();

        // Organiza os dados para o relatório
        $data = [
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'metrics' => [
                'total_proposals' => $proposals->count(),
                'submitted_proposals' => $submittedProposals,
                'won_proposals' => $wonProposals,
                'success_rate' => $successRate,
                'total_value' => $totalValue,
                'status_counts' => $statusCounts,
                'agency_counts' => $agencyCounts,
            ],
            'proposals' => $proposals->map(function ($item) {
                return [
                    'id' => $item->id,
                    'bidding_title' => $item->bidding->title ?? '',
                    'agency' => $item->bidding->agency->name ?? 'Desconhecido',
                    'status' => $item->status,
                    'submission_date' => $item->submission_date ? $item->submission_date->format('d/m/Y') : '',
                    'total_value' => $item->total_value,
                    'discount_percentage' => $item->discount_percentage,
                    'created_at' => $item->created_at->format('d/m/Y'),
                ];
            })->toArray(),
            'monthly_stats' => $monthlyStats,
        ];

        $fileName = "relatorio_propostas_usuario_{$userId}_{$startDate->format('Y_m_d')}.xlsx";
        return $this->generateExcelReport($data, $fileName);
    }

    /**
     * Obtém métricas mensais
     */
    private function getMonthlyMetrics($startDate, $endDate)
    {
        $newBiddings = Bidding::whereBetween('publication_date', [$startDate, $endDate])->count();

        $closedBiddings = Bidding::where('status', 'closed')
                               ->whereBetween('closing_date', [$startDate, $endDate])
                               ->count();

        $totalSubmittedProposals = Proposal::whereIn('status', ['submitted', 'won', 'lost'])
                                         ->whereBetween('submission_date', [$startDate, $endDate])
                                         ->count();

        $totalWonProposals = Proposal::where('status', 'won')
                                   ->whereBetween('updated_at', [$startDate, $endDate])
                                   ->count();

        $totalValueWon = Proposal::where('status', 'won')
                               ->whereBetween('updated_at', [$startDate, $endDate])
                               ->sum('total_value');

        $successRate = $totalSubmittedProposals > 0
                     ? ($totalWonProposals / $totalSubmittedProposals) * 100
                     : 0;

        return [
            'new_biddings' => $newBiddings,
            'closed_biddings' => $closedBiddings,
            'submitted_proposals' => $totalSubmittedProposals,
            'won_proposals' => $totalWonProposals,
            'total_value_won' => $totalValueWon,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Obtém licitações do mês
     */
    private function getMonthlyBiddings($startDate, $endDate)
    {
        return Bidding::with('agency')
                    ->whereBetween('publication_date', [$startDate, $endDate])
                    ->orderBy('publication_date', 'desc')
                    ->get()
                    ->map(function ($bidding) {
                        return [
                            'id' => $bidding->id,
                            'title' => $bidding->title,
                            'agency' => $bidding->agency->name ?? 'Desconhecido',
                            'type' => $bidding->bidding_type,
                            'publication_date' => $bidding->publication_date->format('d/m/Y'),
                            'closing_date' => $bidding->closing_date ? $bidding->closing_date->format('d/m/Y') : '',
                            'status' => $bidding->status,
                            'estimated_value' => $bidding->estimated_value,
                        ];
                    })
                    ->toArray();
    }

    /**
     * Obtém propostas do mês
     */
    private function getMonthlyProposals($startDate, $endDate)
    {
        return Proposal::with(['bidding', 'bidding.agency', 'user'])
                     ->whereIn('status', ['submitted', 'won', 'lost'])
                     ->whereBetween('submission_date', [$startDate, $endDate])
                     ->orderBy('submission_date', 'desc')
                     ->get()
                     ->map(function ($proposal) {
                         return [
                             'id' => $proposal->id,
                             'bidding_title' => $proposal->bidding->title ?? '',
                             'agency' => $proposal->bidding->agency->name ?? 'Desconhecido',
                             'user' => $proposal->user->name ?? '',
                             'status' => $proposal->status,
                             'submission_date' => $proposal->submission_date->format('d/m/Y'),
                             'total_value' => $proposal->total_value,
                         ];
                     })
                     ->toArray();
    }

    /**
     * Obtém desempenho por agência
     */
    private function getAgencyPerformance($startDate, $endDate)
    {
        $agencies = BiddingAgency::withCount([
                                    'biddings' => function ($query) use ($startDate, $endDate) {
                                        $query->whereBetween('publication_date', [$startDate, $endDate]);
                                    }
                                 ])
                                 ->having('biddings_count', '>', 0)
                                 ->orderBy('biddings_count', 'desc')
                                 ->get();

        $results = [];

        foreach ($agencies as $agency) {
            // Conta as propostas enviadas para esta agência
            $submittedProposals = Proposal::whereHas('bidding', function ($query) use ($agency) {
                                        $query->where('agency_id', $agency->id);
                                     })
                                     ->whereIn('status', ['submitted', 'won', 'lost'])
                                     ->whereBetween('submission_date', [$startDate, $endDate])
                                     ->count();

            // Conta as propostas ganhas para esta agência
            $wonProposals = Proposal::whereHas('bidding', function ($query) use ($agency) {
                                  $query->where('agency_id', $agency->id);
                               })
                               ->where('status', 'won')
                               ->whereBetween('updated_at', [$startDate, $endDate])
                               ->count();

            // Valor total ganho com esta agência
            $totalValue = Proposal::whereHas('bidding', function ($query) use ($agency) {
                                $query->where('agency_id', $agency->id);
                             })
                             ->where('status', 'won')
                             ->whereBetween('updated_at', [$startDate, $endDate])
                             ->sum('total_value');

            // Taxa de sucesso para esta agência
            $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

            $results[] = [
                'id' => $agency->id,
                'name' => $agency->name,
                'biddings_count' => $agency->biddings_count,
                'submitted_proposals' => $submittedProposals,
                'won_proposals' => $wonProposals,
                'success_rate' => $successRate,
                'total_value' => $totalValue,
            ];
        }

        return $results;
    }

    /**
     * Compara com o mês anterior
     */
    private function getMonthlyComparison($currentMonthStart)
    {
        $previousMonthStart = $currentMonthStart->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $currentMetrics = $this->getMonthlyMetrics(
            $currentMonthStart,
            $currentMonthStart->copy()->endOfMonth()
        );

        $previousMetrics = $this->getMonthlyMetrics(
            $previousMonthStart,
            $previousMonthEnd
        );

        $comparison = [];

        foreach ($currentMetrics as $key => $currentValue) {
            $previousValue = $previousMetrics[$key] ?? 0;
            $difference = $previousValue > 0
                        ? (($currentValue - $previousValue) / $previousValue) * 100
                        : 0;

            $comparison[$key] = [
                'current' => $currentValue,
                'previous' => $previousValue,
                'difference' => $difference,
                'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral'),
            ];
        }

        return $comparison;
    }

    /**
     * Obtém métricas para uma agência específica
     */
    private function getAgencyMetrics(BiddingAgency $agency, $startDate, $endDate)
    {
        $totalBiddings = Bidding::where('agency_id', $agency->id)
                              ->whereBetween('publication_date', [$startDate, $endDate])
                              ->count();

        $activeBiddings = Bidding::where('agency_id', $agency->id)
                               ->active()
                               ->count();

        $submittedProposals = Proposal::whereHas('bidding', function ($query) use ($agency) {
                                    $query->where('agency_id', $agency->id);
                                 })
                                 ->whereIn('status', ['submitted', 'won', 'lost'])
                                 ->whereBetween('submission_date', [$startDate, $endDate])
                                 ->count();

        $wonProposals = Proposal::whereHas('bidding', function ($query) use ($agency) {
                              $query->where('agency_id', $agency->id);
                           })
                           ->where('status', 'won')
                           ->whereBetween('updated_at', [$startDate, $endDate])
                           ->count();

        $totalValue = Proposal::whereHas('bidding', function ($query) use ($agency) {
                            $query->where('agency_id', $agency->id);
                         })
                         ->where('status', 'won')
                         ->whereBetween('updated_at', [$startDate, $endDate])
                         ->sum('total_value');

        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

        return [
            'total_biddings' => $totalBiddings,
            'active_biddings' => $activeBiddings,
            'submitted_proposals' => $submittedProposals,
            'won_proposals' => $wonProposals,
            'total_value' => $totalValue,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Obtém licitações de uma agência específica
     */
    private function getAgencyBiddings(BiddingAgency $agency, $startDate, $endDate)
    {
        return Bidding::where('agency_id', $agency->id)
                    ->whereBetween('publication_date', [$startDate, $endDate])
                    ->orderBy('publication_date', 'desc')
                    ->get()
                    ->map(function ($bidding) {
                        return [
                            'id' => $bidding->id,
                            'title' => $bidding->title,
                            'type' => $bidding->bidding_type,
                            'publication_date' => $bidding->publication_date->format('d/m/Y'),
                            'closing_date' => $bidding->closing_date ? $bidding->closing_date->format('d/m/Y') : '',
                            'status' => $bidding->status,
                            'estimated_value' => $bidding->estimated_value,
                        ];
                    })
                    ->toArray();
    }

    /**
     * Obtém propostas para uma agência específica
     */
    private function getAgencyProposals(BiddingAgency $agency, $startDate, $endDate)
    {
        return Proposal::whereHas('bidding', function ($query) use ($agency) {
                      $query->where('agency_id', $agency->id);
                   })
                   ->with(['bidding', 'user'])
                   ->whereIn('status', ['submitted', 'won', 'lost'])
                   ->whereBetween('submission_date', [$startDate, $endDate])
                   ->orderBy('submission_date', 'desc')
                   ->get()
                   ->map(function ($proposal) {
                       return [
                           'id' => $proposal->id,
                           'bidding_title' => $proposal->bidding->title ?? '',
                           'user' => $proposal->user->name ?? '',
                           'status' => $proposal->status,
                           'submission_date' => $proposal->submission_date->format('d/m/Y'),
                           'total_value' => $proposal->total_value,
                           'discount_percentage' => $proposal->discount_percentage,
                       ];
                   })
                   ->toArray();
    }

    /**
     * Obtém estatísticas mensais para uma agência
     */
    private function getAgencyMonthlyStats(BiddingAgency $agency, $startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current->lte($endDate)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $biddings = Bidding::where('agency_id', $agency->id)
                             ->whereBetween('publication_date', [$monthStart, $monthEnd])
                             ->count();

            $submitted = Proposal::whereHas('bidding', function ($query) use ($agency) {
                               $query->where('agency_id', $agency->id);
                            })
                            ->whereIn('status', ['submitted', 'won', 'lost'])
                            ->whereBetween('submission_date', [$monthStart, $monthEnd])
                            ->count();

            $won = Proposal::whereHas('bidding', function ($query) use ($agency) {
                         $query->where('agency_id', $agency->id);
                      })
                      ->where('status', 'won')
                      ->whereBetween('updated_at', [$monthStart, $monthEnd])
                      ->count();

            $value = Proposal::whereHas('bidding', function ($query) use ($agency) {
                           $query->where('agency_id', $agency->id);
                        })
                        ->where('status', 'won')
                        ->whereBetween('updated_at', [$monthStart, $monthEnd])
                        ->sum('total_value');

            $months[] = [
                'month' => $current->format('M/Y'),
                'biddings' => $biddings,
                'submitted_proposals' => $submitted,
                'won_proposals' => $won,
                'total_value' => $value,
            ];

            $current->addMonth();
        }

        return $months;
    }

    /**
     * Gera o arquivo Excel do relatório
     */
    private function generateExcelReport($data, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Estilo para títulos
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // Estilo para cabeçalhos
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // A implementação completa do método generateExcelReport incluiria
        // a formatação específica de cada relatório, preenchimento de dados, etc.
        // Aqui está uma versão simplificada da lógica:

        // Configura o relatório com base no tipo de dados recebidos
        if (isset($data['metrics'])) {
            // Configuração de cabeçalho
            $sheet->setCellValue('A1', 'RELATÓRIO DE DESEMPENHO');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->applyFromArray($titleStyle);

            // Período do relatório
            $sheet->setCellValue('A2', 'Período: ' . ($data['period'] ?? ''));
            $sheet->setCellValue('A3', 'Gerado em: ' . ($data['generated_at'] ?? ''));

            // Seção de métricas
            $sheet->setCellValue('A5', 'MÉTRICAS PRINCIPAIS');
            $sheet->mergeCells('A5:C5');
            $sheet->getStyle('A5')->applyFromArray($headerStyle);

            $row = 6;
            foreach ($data['metrics'] as $key => $value) {
                if (!is_array($value)) {
                    $sheet->setCellValue('A' . $row, str_replace('_', ' ', ucfirst($key)));
                    $sheet->setCellValue('B' . $row, is_numeric($value) && !in_array($key, ['success_rate']) ?
                                        number_format($value, 2, ',', '.') : $value);
                    $row++;
                }
            }

            // Se houver dados de licitações
            if (isset($data['biddings']) && count($data['biddings']) > 0) {
                $row += 2;
                $sheet->setCellValue('A' . $row, 'LICITAÇÕES');
                $sheet->mergeCells('A' . $row . ':G' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);

                $row++;
                // Cabeçalhos da tabela de licitações
                $headers = array_keys($data['biddings'][0]);
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, str_replace('_', ' ', ucfirst($header)));
                    $sheet->getStyle($col . $row)->applyFromArray($headerStyle);
                    $col++;
                }

                $row++;
                // Dados das licitações
                foreach ($data['biddings'] as $bidding) {
                    $col = 'A';
                    foreach ($bidding as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    $row++;
                }
            }

            // Similar para propostas, agências, etc.
        }

        // Salva o arquivo
        $writer = new Xlsx($spreadsheet);
        $path = 'reports/' . $fileName;
        $fullPath = storage_path('app/' . $path);

        // Garante que o diretório exista
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($fullPath);

        return $path;
    }
}
