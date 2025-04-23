<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportBiddingsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $biddings;

    public function __construct(array $biddings)
    {
        $this->biddings = $biddings;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->biddings;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if (empty($this->biddings)) {
            return [];
        }

        // Usa as chaves do primeiro item como cabeçalhos
        $headers = array_keys($this->biddings[0]);

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
        return 'Licitações';
    }
}
