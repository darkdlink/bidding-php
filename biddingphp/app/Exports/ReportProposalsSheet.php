<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportProposalsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $proposals;

    public function __construct(array $proposals)
    {
        $this->proposals = $proposals;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->proposals;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if (empty($this->proposals)) {
            return [];
        }

        // Usa as chaves do primeiro item como cabeçalhos
        $headers = array_keys($this->proposals[0]);

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
        return 'Propostas';
    }
}
