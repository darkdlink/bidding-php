<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe a lista de notificações do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());

        // Filtros
        if ($request->has('read') && $request->read !== '') {
            $query->where('read', (bool) $request->read);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Ordenação padrão: mais recentes primeiro
        $query->orderBy('created_at', 'desc');

        // Paginação
        $notifications = $query->paginate(15);

        // Tipos de notificações para o filtro
        $notificationTypes = Notification::where('user_id', Auth::id())
                                       ->select('type')
                                       ->distinct()
                                       ->pluck('type');

        return view('notifications.index', compact('notifications', 'notificationTypes'));
    }

    /**
     * Marca uma notificação como lida.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Notification $notification)
    {
        // Verifica se a notificação pertence ao usuário logado
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->read = true;
        $notification->save();

        return back()->with('success', 'Notificação marcada como lida.');
    }

    /**
     * Marca todas as notificações do usuário como lidas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
                   ->where('read', false)
                   ->update(['read' => true]);

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    /**
     * Exclui uma notificação.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Notification $notification)
    {
        // Verifica se a notificação pertence ao usuário logado
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notificação excluída com sucesso.');
    }

    /**
     * Exclui todas as notificações lidas do usuário.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearRead()
    {
        Notification::where('user_id', Auth::id())
                   ->where('read', true)
                   ->delete();

        return back()->with('success', 'Todas as notificações lidas foram excluídas.');
    }

    /**
     * Retorna a contagem de notificações não lidas para o usuário (para uso em Ajax).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
                            ->where('read', false)
                            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Retorna as notificações recentes para o usuário (para uso em Ajax).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get()
                                    ->map(function ($notification) {
                                        return [
                                            'id' => $notification->id,
                                            'title' => $notification->title,
                                            'message' => $notification->message,
                                            'type' => $notification->type,
                                            'read' => $notification->read,
                                            'created_at' => $notification->created_at->diffForHumans(),
                                            'url' => $this->getNotificationUrl($notification)
                                        ];
                                    });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Determina a URL para redirecionamento com base no tipo de notificação.
     *
     * @param  \App\Models\Notification  $notification
     * @return string|null
     */
    private function getNotificationUrl(Notification $notification)
    {
        // Se tiver um tipo e ID relacionado, gera a URL apropriada
        if ($notification->related_type && $notification->related_id) {
            switch ($notification->related_type) {
                case 'bidding':
                    return route('biddings.show', $notification->related_id);
                case 'proposal':
                    return route('proposals.show', $notification->related_id);
                default:
                    return null;
            }
        }

        return null;
    }

    /**
     * Exibe detalhes de uma notificação e redireciona para o conteúdo relacionado.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Notification $notification)
    {
        // Verifica se a notificação pertence ao usuário logado
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        // Marca como lida
        if (!$notification->read) {
            $notification->read = true;
            $notification->save();
        }

        // Redireciona para o conteúdo relacionado, se houver
        $url = $this->getNotificationUrl($notification);

        if ($url) {
            return redirect($url);
        }

        // Se não tiver URL relacionada, volta para a lista
        return redirect()->route('notifications.index')
                       ->with('info', 'Notificação visualizada, mas sem conteúdo específico para exibir.');
    }
}
