{{-- Componente para exibir o status de uma licitação --}}
@if(isset($bidding))
    @if($bidding->status == 'published')
        <span class="badge badge-success">Publicada</span>
    @elseif($bidding->status == 'in_progress')
        <span class="badge badge-info">Em Andamento</span>
    @elseif($bidding->status == 'closed')
        <span class="badge badge-secondary">Fechada</span>
    @elseif($bidding->status == 'awarded')
        <span class="badge badge-primary">Adjudicada</span>
    @elseif($bidding->status == 'cancelled')
        <span class="badge badge-danger">Cancelada</span>
    @else
        <span class="badge badge-warning">Rascunho</span>
    @endif
@endif
