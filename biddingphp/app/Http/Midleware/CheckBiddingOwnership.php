<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Bidding;

class CheckBiddingOwnership
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
        $biddingId = $request->route('bidding');

        if ($biddingId instanceof Bidding) {
            $bidding = $biddingId;
        } else {
            $bidding = Bidding::findOrFail($biddingId);
        }

        // Administradores podem acessar qualquer licitação
        if ($request->user()->can('access-admin')) {
            return $next($request);
        }

        // Verifica se o usuário tem permissão para gerenciar esta licitação específica
        if ($request->user()->can('update', $bidding)) {
            return $next($request);
        }

        abort(403, 'Você não tem permissão para gerenciar esta licitação.');
    }
}
