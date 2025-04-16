<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Routing\Controller;

class VerificationController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Marca o e-mail do usuário como verificado.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'E-mail já foi verificado.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('dashboard')->with('success', 'E-mail verificado com sucesso!');
    }

    /**
     * Exibe a página de notificação de verificação de e-mail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->route('dashboard')
                    : view('auth.verify');
    }

    /**
     * Reenvia o e-mail de verificação.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'E-mail já foi verificado.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'E-mail de verificação reenviado!');
    }

    /**
     * Exibe a página de verificação bem-sucedida.
     *
     * @return \Illuminate\View\View
     */
    public function verified()
    {
        return view('auth.verified');
    }

    /**
     * Exibe a página de verificação expirada.
     *
     * @return \Illuminate\View\View
     */
    public function expired()
    {
        return view('auth.verification-expired');
    }
}
