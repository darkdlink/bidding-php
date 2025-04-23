<?php

namespace App\Exports;

use App\Models\Bidding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BiddingItemsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $bidding;

    public function __construct(Bidding $bidding)
    {
        $this->bidding = $bidding;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->bidding->items;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número',
            'Descrição',
            'Quantidade',
            'Unidade',
            'Preço Unitário Est.',
            'Preço Total Est.'
        ];
    }

    /**
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->item_number ?? 'N/A',
            $item->description,
            $item->quantity,
            $item->unit ?? 'un',
            $item->estimated_unit_price ? 'R$ ' . number_format($item->estimated_unit_price, 2, ',', '.') : 'N/A',
            $item->estimated_unit_price ? 'R$ ' . number_format($item->estimated_unit_price * $item->quantity, 2, ',', '.') : 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Itens - Licitação ' . $this->bidding->id;
    }
}
