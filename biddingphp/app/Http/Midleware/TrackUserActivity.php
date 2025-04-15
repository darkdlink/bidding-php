<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackUserActivity
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
        if (Auth::check()) {
            // Atualiza o timestamp de última atividade
            $user = Auth::user();
            $user->last_activity_at = Carbon::now();
            $user->save();

            // Registra o acesso a página (opcional)
            // \App\Models\UserActivity::create([
            //     'user_id' => $user->id,
            //     'ip_address' => $request->ip(),
            //     'user_agent' => $request->userAgent(),
            //     'url' => $request->fullUrl(),
            //     'method' => $request->method(),
            // ]);
        }

        return $next($request);
    }
}
