<?php

namespace App\Exports;

use App\Models\Proposal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProposalExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $proposal;

    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->proposal->items;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Item',
            'Descrição',
            'Quantidade',
            'Unidade',
            'Preço Unitário',
            'Preço Total',
            'Observações'
        ];
    }

    /**
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->bidding_item->item_number ?? $item->id,
            $item->bidding_item->description,
            $item->bidding_item->quantity,
            $item->bidding_item->unit ?? 'un',
            $item->unit_price,
            $item->total_price,
            $item->notes
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Proposta ' . $this->proposal->id;
    }
}
