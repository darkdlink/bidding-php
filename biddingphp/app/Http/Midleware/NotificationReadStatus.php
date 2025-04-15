<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationReadStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Marca a notificação como lida automaticamente se estiver visualizando detalhes
        $notificationId = $request->route('notification');

        if ($notificationId) {
            $notification = Notification::find($notificationId);

            if ($notification && $notification->user_id === $request->user()->id) {
                $notification->markAsRead();
            }
        }

        return $response;
    }
}
