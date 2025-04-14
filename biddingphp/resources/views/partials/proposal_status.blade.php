{{-- Componente para exibir o status de uma proposta --}}
@if(isset($proposal))
    @if($proposal->status == 'submitted')
        <span class="badge badge-info">Enviada</span>
    @elseif($proposal->status == 'won')
        <span class="badge badge-success">Vencedora</span>
    @elseif($proposal->status == 'lost')
        <span class="badge badge-danger">Perdida</span>
    @elseif($proposal->status == 'cancelled')
        <span class="badge badge-secondary">Cancelada</span>
    @else
        <span class="badge badge-warning">Rascunho</span>
    @endif
@endif
