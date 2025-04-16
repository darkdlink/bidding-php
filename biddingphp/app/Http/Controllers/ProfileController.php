<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe o formulário para edição do perfil do usuário.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Atualiza as informações do perfil do usuário.
     *
     * @param  \App\Http\Requests\UserProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserProfileRequest $request)
    {
        $user = Auth::user();

        // Dados básicos
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        // Atualiza senha se informada
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Upload de imagem de perfil
        if ($request->hasFile('profile_image')) {
            // Remove imagem anterior se existir
            if ($user->profile_image && Storage::exists($user->profile_image)) {
                Storage::delete($user->profile_image);
            }

            // Salva nova imagem
            $path = $request->file('profile_image')->store('profile_images');
            $user->profile_image = $path;
        }

        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Exibe a página de configurações do usuário.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $user = Auth::user();
        $notificationPreferences = $user->notification_preferences ?? [
            'email' => true,
            'system' => true
        ];

        return view('profile.settings', compact('user', 'notificationPreferences'));
    }

    /**
     * Atualiza as configurações do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'notification_preferences.email' => 'boolean',
            'notification_preferences.system' => 'boolean',
            'locale' => 'nullable|in:pt_BR,en',
            'theme' => 'nullable|in:light,dark',
        ]);

        $user = Auth::user();

        // Salva preferências de notificação
        $user->notification_preferences = $request->input('notification_preferences', [
            'email' => true,
            'system' => true
        ]);

        // Salva preferências de interface
        $user->locale = $request->input('locale');
        $user->theme = $request->input('theme');

        $user->save();

        // Atualiza sessão para refletir mudanças imediatamente
        if ($request->filled('locale')) {
            session(['locale' => $request->input('locale')]);
        }

        return redirect()->route('profile.settings')
                        ->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Remove a imagem de perfil do usuário.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeProfileImage()
    {
        $user = Auth::user();

        if ($user->profile_image && Storage::exists($user->profile_image)) {
            Storage::delete($user->profile_image);
        }

        $user->profile_image = null;
        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'Imagem de perfil removida com sucesso!');
    }

    /**
     * Exibe as estatísticas do usuário.
     *
     * @return \Illuminate\View\View
     */
    public function stats()
    {
        $user = Auth::user();

        // Total de propostas
        $totalProposals = $user->proposals()->count();

        // Propostas por status
        $proposalsByStatus = $user->proposals()
                                ->select('status', \DB::raw('count(*) as total'))
                                ->groupBy('status')
                                ->pluck('total', 'status')
                                ->toArray();

        // Total de licitações com proposta
        $totalBiddings = $user->proposals()
                             ->distinct('bidding_id')
                             ->count('bidding_id');

        // Valor total de propostas ganhas
        $totalValueWon = $user->proposals()
                             ->where('status', 'won')
                             ->sum('total_value');

        // Taxa de sucesso
        $submittedProposals = $user->proposals()
                                  ->whereIn('status', ['submitted', 'won', 'lost'])
                                  ->count();
        $wonProposals = $user->proposals()
                            ->where('status', 'won')
                            ->count();
        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

        return view('profile.stats', compact(
            'user',
            'totalProposals',
            'proposalsByStatus',
            'totalBiddings',
            'totalValueWon',
            'submittedProposals',
            'wonProposals',
            'successRate'
        ));
    }
}
