<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class ReportExport implements WithMultipleSheets
{
    use Exportable;

    protected $data;
    protected $type;

    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Planilha principal de dados
        $sheets[] = new ReportMainSheet($this->data, $this->type);

        // Planilhas adicionais baseadas no tipo de relatório
        switch ($this->type) {
            case 'monthly':
                if (isset($this->data['biddings'])) {
                    $sheets[] = new ReportBiddingsSheet($this->data['biddings']);
                }
                if (isset($this->data['proposals'])) {
                    $sheets[] = new ReportProposalsSheet($this->data['proposals']);
                }
                break;

            case 'agency':
                if (isset($this->data['biddings'])) {
                    $sheets[] = new ReportBiddingsSheet($this->data['biddings']);
                }
                if (isset($this->data['monthly_stats'])) {
                    $sheets[] = new ReportMonthlyStatsSheet($this->data['monthly_stats']);
                }
                break;

            case 'performance':
                if (isset($this->data['proposals'])) {
                    $sheets[] = new ReportProposalsSheet($this->data['proposals']);
                }
                if (isset($this->data['monthly_stats'])) {
                    $sheets[] = new ReportMonthlyStatsSheet($this->data['monthly_stats']);
                }
                break;
        }

        return $sheets;
    }
}
