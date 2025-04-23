<?php

namespace App\Exports;

use App\Models\BiddingAgency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BiddingAgencyExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $agencies;

    public function __construct($agencies)
    {
        $this->agencies = $agencies;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->agencies;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Código',
            'Website',
            'Informações de Contato',
            'Total de Licitações',
            'Licitações Ativas',
            'Data de Cadastro'
        ];
    }

    /**
     * @param BiddingAgency $agency
     * @return array
     */
    public function map($agency): array
    {
        // Carrega contagens relacionadas
        $totalBiddings = $agency->biddings()->count();
        $activeBiddings = $agency->biddings()->active()->count();

        return [
            $agency->id,
            $agency->name,
            $agency->code ?? 'N/A',
            $agency->website ?? 'N/A',
            $agency->contact_info ?? 'N/A',
            $totalBiddings,
            $activeBiddings,
            $agency->created_at->format('d/m/Y')
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Órgãos Licitantes';
    }
}
