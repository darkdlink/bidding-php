<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ReportMonthlyStatsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithCharts
{
    protected $stats;

    public function __construct(array $stats)
    {
        $this->stats = $stats;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->stats;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if (empty($this->stats)) {
            return [];
        }

        // Usa as chaves do primeiro item como cabeçalhos
        $headers = array_keys($this->stats[0]);

        // Formata os cabeçalhos para exibição
        return array_map(function($header) {
            return ucfirst(str_replace('_', ' ', $header));
        }, $headers);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Estatísticas Mensais';
    }

    /**
     * @return array
     */
    public function charts()
    {
        if (empty($this->stats)) {
            return [];
        }

        $numRows = count($this->stats) + 1; // +1 para o cabeçalho

        // Define as séries de dados para o gráfico
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Estatísticas Mensais!$B$1', null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Estatísticas Mensais!$C$1', null, 1),
        ];

        // Valores para o eixo X (meses)
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Estatísticas Mensais!$A$2:$A$' . $numRows, null, $numRows - 1),
        ];

        // Valores das séries de dados
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Estatísticas Mensais!$B$2:$B$' . $numRows, null, $numRows - 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Estatísticas Mensais!$C$2:$C$' . $numRows, null, $numRows - 1),
        ];

        // Constrói a série de dados
        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        // Define a área do gráfico
        $plotArea = new PlotArea(null, [$series]);

        // Define a legenda
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        // Define o título do gráfico
        $title = new Title('Evolução Mensal');

        // Cria o gráfico
        $chart = new Chart(
            'chart1',
            $title,
            $legend,
            $plotArea
        );

        // Define a posição do gráfico
        $chart->setTopLeftPosition('A' . ($numRows + 2));
        $chart->setBottomRightPosition('H' . ($numRows + 15));

        return [$chart];
    }
}
