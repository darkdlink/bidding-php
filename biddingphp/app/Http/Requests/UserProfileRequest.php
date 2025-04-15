<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Usuário só pode editar seu próprio perfil
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'current_password' => 'nullable|required_with:password|password',
            'password' => 'nullable|min:8|confirmed',
            'profile_image' => 'nullable|image|max:2048',
            'notification_preferences' => 'nullable|array',
            'notification_preferences.email' => 'boolean',
            'notification_preferences.system' => 'boolean',
        ];
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
            'current_password.required_with' => 'A senha atual é obrigatória quando você quer alterá-la.',
            'current_password.password' => 'A senha atual está incorreta.',
            'password.min' => 'A nova senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não corresponde.',
            'profile_image.image' => 'O arquivo deve ser uma imagem.',
            'profile_image.max' => 'A imagem não pode ser maior que 2MB.',
        ];
    }
}
