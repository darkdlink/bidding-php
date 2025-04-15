<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Proposal;

class CheckProposalOwnership
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
        $proposalId = $request->route('proposal');

        if ($proposalId instanceof Proposal) {
            $proposal = $proposalId;
        } else {
            $proposal = Proposal::findOrFail($proposalId);
        }

        // Administradores podem acessar qualquer proposta
        if ($request->user()->can('access-admin')) {
            return $next($request);
        }

        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id === $request->user()->id) {
            return $next($request);
        }

        abort(403, 'Você não tem permissão para acessar esta proposta.');
    }
}
