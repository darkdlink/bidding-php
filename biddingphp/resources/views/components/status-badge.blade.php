@if($status == 'published')
    <span class="badge badge-success">Publicada</span>
@elseif($status == 'in_progress')
    <span class="badge badge-info">Em Andamento</span>
@elseif($status == 'closed')
    <span class="badge badge-secondary">Fechada</span>
@elseif($status == 'awarded')
    <span class="badge badge-primary">Adjudicada</span>
@elseif($status == 'cancelled')
    <span class="badge badge-danger">Cancelada</span>
@elseif($status == 'draft')
    <span class="badge badge-warning">Rascunho</span>
@elseif($status == 'submitted')
    <span class="badge badge-info">Enviada</span>
@elseif($status == 'won')
    <span class="badge badge-success">Vencedora</span>
@elseif($status == 'lost')
    <span class="badge badge-danger">Perdida</span>
@else
    <span class="badge badge-secondary">{{ $status }}</span>
@endif
