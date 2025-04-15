<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ConfigureLocale
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
        // Define idioma padrão
        $locale = config('app.locale');

        // Se o usuário estiver logado e tiver preferência de idioma
        if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
        }

        // Se houver um idioma definido na sessão
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        // Se houver um idioma definido na query string
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            $request->session()->put('locale', $locale);
        }

        // Configura o idioma da aplicação
        App::setLocale($locale);

        return $next($request);
    }
}
