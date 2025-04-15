<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\BiddingAgency;
use App\Models\Proposal;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportGenerationService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('auth');
    }

    /**
     * Gera relatório mensal
     */
    public function monthly(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Gera o relatório
        $reportPath = $this->reportService->generateMonthlyPerformanceReport($month, $year);

        // Informações para o frontend
        $reportUrl = route('reports.download', basename($reportPath));
        $reportDate = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return view('reports.monthly', compact('reportUrl', 'reportDate', 'month', 'year'));
    }

    /**
     * Gera relatório por agência
     */
    public function agency(Request $request, BiddingAgency $agency)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Gera o relatório
        $reportPath = $this->reportService->generateAgencyReport($agency, $startDate, $endDate);

        // Informações para o frontend
        $reportUrl = route('reports.download', basename($reportPath));

        return view('reports.agency', compact('reportUrl', 'agency', 'startDate', 'endDate'));
    }

    /**
     * Gera relatório de desempenho do usuário
     */
    public function performance(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Gera o relatório
        $reportPath = $this->reportService->generateUserProposalsReport(Auth::id(), $startDate, $endDate);

        // Informações para o frontend
        $reportUrl = route('reports.download', basename($reportPath));

        return view('reports.performance', compact('reportUrl', 'startDate', 'endDate'));
    }

    /**
     * Download de relatório
     */
    public function download($file)
    {
        $path = 'reports/' . $file;

        if (!Storage::exists($path)) {
            abort(404);
        }

        return Storage::download($path);
    }
}
