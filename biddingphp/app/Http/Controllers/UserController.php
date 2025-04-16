<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\AdminUserRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage-users']);
    }

    /**
     * Exibe a lista de usuários.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('active') && $request->active !== '') {
            $query->where('active', (bool) $request->active);
        }

        // Ordenação
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $users = $query->paginate(15);

        // Roles para filtro
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Exibe o formulário para criar um novo usuário.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Armazena um novo usuário no banco de dados.
     *
     * @param  \App\Http\Requests\AdminUserRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AdminUserRequest $request)
    {
        try {
            DB::beginTransaction();

            // Cria o usuário
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => $request->has('active'),
            ]);

            // Atribui papéis (roles)
            if ($request->has('roles')) {
                $user->assignRole($request->roles);
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                            ->with('success', 'Usuário criado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de um usuário específico.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $user->load('roles');

        // Estatísticas do usuário
        $proposalsCount = $user->proposals()->count();
        $proposalsByStatus = $user->proposals()
                                ->select('status', DB::raw('count(*) as total'))
                                ->groupBy('status')
                                ->pluck('total', 'status')
                                ->toArray();

        $wonProposals = $user->proposals()->where('status', 'won')->count();
        $submittedProposals = $user->proposals()->whereIn('status', ['submitted', 'won', 'lost'])->count();
        $successRate = $submittedProposals > 0 ? ($wonProposals / $submittedProposals) * 100 : 0;

        $totalValueWon = $user->proposals()->where('status', 'won')->sum('total_value');

        // Últimas atividades
        $recentProposals = $user->proposals()
                              ->with('bidding')
                              ->orderBy('created_at', 'desc')
                              ->limit(5)
                              ->get();

        $lastLogin = $user->last_login_at;

        return view('admin.users.show', compact(
            'user',
            'proposalsCount',
            'proposalsByStatus',
            'successRate',
            'totalValueWon',
            'recentProposals',
            'lastLogin'
        ));
    }

    /**
     * Exibe o formulário para editar um usuário.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Atualiza um usuário específico no banco de dados.
     *
     * @param  \App\Http\Requests\AdminUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AdminUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            // Atualiza dados básicos
            $user->name = $request->name;
            $user->email = $request->email;
            $user->active = $request->has('active');

            // Atualiza senha se fornecida
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Atualiza papéis (roles)
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            } else {
                $user->syncRoles([]);
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                            ->with('success', 'Usuário atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Remove um usuário do banco de dados.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Impede exclusão do próprio usuário
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        try {
            // Verifica se o usuário tem propostas
            $proposalsCount = $user->proposals()->count();

            if ($proposalsCount > 0) {
                return back()->with('error', 'Este usuário possui propostas associadas e não pode ser excluído.');
            }

            // Remove imagem de perfil
            if ($user->profile_image) {
                \Storage::delete($user->profile_image);
            }

            // Remove usuário
            $user->delete();

            return redirect()->route('admin.users.index')
                            ->with('success', 'Usuário excluído com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir usuário: ' . $e->getMessage());
        }
    }

    /**
     * Ativa ou desativa um usuário.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(User $user)
    {
        // Impede desativação do próprio usuário
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário {$status} com sucesso.");
    }

    /**
     * Exibe o formulário para enviar um e-mail ao usuário.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function composeEmail(User $user)
    {
        return view('admin.users.email', compact('user'));
    }

    /**
     * Envia um e-mail ao usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            \Mail::to($user)->send(new \App\Mail\AdminMessage(
                $request->subject,
                $request->message,
                auth()->user()
            ));

            return redirect()->route('admin.users.show', $user)
                            ->with('success', 'E-mail enviado com sucesso.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
}
