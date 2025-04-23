<?php

namespace App\Exports;

use App\Models\Bidding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BiddingExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $biddings;
    protected $withItems;

    public function __construct($biddings, $withItems = false)
    {
        $this->biddings = $biddings;
        $this->withItems = $withItems;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->biddings;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'ID Externo',
            'Título',
            'Tipo',
            'Modalidade',
            'Órgão',
            'Data de Publicação',
            'Data de Abertura',
            'Data de Fechamento',
            'Valor Estimado',
            'Status'
        ];
    }

    /**
     * @param Bidding $bidding
     * @return array
     */
    public function map($bidding): array
    {
        return [
            $bidding->id,
            $bidding->external_id ?? 'N/A',
            $bidding->title,
            $bidding->bidding_type,
            $bidding->modality ?? 'N/A',
            $bidding->agency->name ?? 'N/A',
            $bidding->publication_date ? $bidding->publication_date->format('d/m/Y') : 'N/A',
            $bidding->opening_date ? $bidding->opening_date->format('d/m/Y H:i') : 'N/A',
            $bidding->closing_date ? $bidding->closing_date->format('d/m/Y H:i') : 'N/A',
            $bidding->estimated_value ? 'R$ ' . number_format($bidding->estimated_value, 2, ',', '.') : 'N/A',
            ucfirst($bidding->status)
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Licitações';
    }

    /**
     * Create additional sheets if items are requested
     */
    public function sheets(): array
    {
        $sheets = [$this];

        if ($this->withItems) {
            // Adiciona planilha com itens para cada licitação
            foreach ($this->biddings as $bidding) {
                $sheets[] = new BiddingItemsExport($bidding);
            }
        }

        return $sheets;
    }
}
