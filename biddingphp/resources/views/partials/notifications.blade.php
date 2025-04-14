{{-- Componente para exibição de notificações --}}
<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
    <h6 class="dropdown-header">
        Centro de Notificações
    </h6>

    @if(isset($notifications) && $notifications->count() > 0)
        @foreach($notifications as $notification)
            <a class="dropdown-item d-flex align-items-center notification-item {{ $notification->read ? 'read' : '' }}" href="{{ route('notifications.show', $notification) }}">
                <div class="mr-3">
                    @if($notification->type == 'bidding')
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    @elseif($notification->type == 'bidding_update')
                        <div class="icon-circle bg-info">
                            <i class="fas fa-sync text-white"></i>
                        </div>
                    @elseif($notification->type == 'bidding_closing')
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    @elseif($notification->type == 'bidding_result')
                        <div class="icon-circle bg-success">
                            <i class="fas fa-trophy text-white"></i>
                        </div>
                    @elseif($notification->type == 'proposal_submitted')
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-paper-plane text-white"></i>
                        </div>
                    @else
                        <div class="icon-circle bg-secondary">
                            <i class="fas fa-bell text-white"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <div class="small text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                    <span class="{{ $notification->read ? 'text-gray-600' : 'font-weight-bold' }}">{{ $notification->title }}</span>
                    <div class="small text-gray-600">{{ Str::limit($notification->message, 70) }}</div>
                </div>
            </a>
        @endforeach

        <a class="dropdown-item text-center small text-gray-500" href="{{ route('notifications.index') }}">Ver Todas as Notificações</a>
    @else
        <a class="dropdown-item d-flex align-items-center" href="#">
            <div class="mr-3">
                <div class="icon-circle bg-secondary">
                    <i class="fas fa-bell-slash text-white"></i>
                </div>
            </div>
            <div>
                <div class="small text-gray-500">Agora</div>
                <span>Nenhuma notificação disponível</span>
            </div>
        </a>
    @endif

    @if(isset($unreadCount) && $unreadCount > 0)
        <a class="dropdown-item text-center small text-primary" href="{{ route('notifications.mark-all-read') }}">
            Marcar Todas como Lidas
        </a>
    @endif
</div>
