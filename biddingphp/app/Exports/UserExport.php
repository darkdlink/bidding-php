<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UserExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'E-mail',
            'Papéis',
            'Status',
            'Propostas Enviadas',
            'Propostas Ganhas',
            'Taxa de Sucesso',
            'Data de Cadastro',
            'Último Login'
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    public function map($user): array
    {
        // Calcula estatísticas do usuário
        $submittedProposals = $user->proposals()->whereIn('status', ['submitted', 'won', 'lost'])->count();
        $wonProposals = $user->proposals()->where('status', 'won')->count();
        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->active ? 'Ativo' : 'Inativo',
            $submittedProposals,
            $wonProposals,
            number_format($successRate, 2) . '%',
            $user->created_at->format('d/m/Y'),
            $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Usuários';
    }
}
