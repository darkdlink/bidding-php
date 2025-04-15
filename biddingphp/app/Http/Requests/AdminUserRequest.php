<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
            ],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'active' => 'boolean',
        ];

        // Se estiver criando um novo usuário, senha é obrigatória
        // Se estiver editando, senha é opcional
        if ($this->isMethod('post')) {
            $rules['email'][] = 'unique:users';
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $userId = $this->route('user')->id;
            $rules['email'][] = Rule::unique('users')->ignore($userId);
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'roles.required' => 'Pelo menos um papel (role) deve ser atribuído.',
            'roles.*.exists' => 'Um dos papéis selecionados é inválido.',
            'password.required' => 'A senha é obrigatória ao criar um novo usuário.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
        ];
    }
}
