<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Proposal;

class StoreProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $proposal = $this->route('proposal');

        // Verifica se a proposta pertence ao usuário e está em rascunho
        return $proposal && $proposal->user_id === $this->user()->id && $proposal->canEdit();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'notes' => 'nullable|string',

            // Itens da proposta
            'items' => 'required|array',
            'items.*' => 'array',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',

            // Desconto geral
            'apply_discount' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:' . config('bidding.proposals.max_discount', 50),

            // Anexos
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'attachment_description' => 'nullable|string|max:255',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'exists:attachments,id',
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
            'items.required' => 'A proposta deve conter itens.',
            'items.*.unit_price.required' => 'O preço unitário é obrigatório para todos os itens.',
            'items.*.unit_price.numeric' => 'O preço unitário deve ser um valor numérico.',
            'items.*.unit_price.min' => 'O preço unitário deve ser maior que zero.',

            'discount_percentage.min' => 'O percentual de desconto não pode ser negativo.',
            'discount_percentage.max' => 'O percentual de desconto não pode ser maior que :max%.',

            'attachments.*.max' => 'O arquivo não pode ser maior que 10MB.',
            'attachments.*.mimes' => 'O arquivo deve ser dos tipos: pdf, doc, docx, xls, xlsx, jpg, jpeg, png.',
        ];
    }
}
