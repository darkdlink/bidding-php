<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBiddingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Verifica se o usuário tem permissão para criar/editar licitações
        return $this->user()->can('create', \App\Models\Bidding::class) ||
               $this->user()->can('update', $this->route('bidding'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'agency_id' => 'required|exists:bidding_agencies,id',
            'bidding_type' => 'required|in:pregão,concorrência,tomada de preços,convite,leilão,concurso,outros',
            'modality' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published,in_progress,closed,cancelled,awarded',
            'publication_date' => 'nullable|date',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:opening_date',
            'estimated_value' => 'nullable|numeric|min:0',
            'document_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'nullable|string|max:20',

            // Itens da licitação
            'items' => 'sometimes|array',
            'items.*.id' => 'nullable|exists:bidding_items,id',
            'items.*.item_number' => 'nullable|string|max:20',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.estimated_unit_price' => 'nullable|numeric|min:0',

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
            'title.required' => 'O título da licitação é obrigatório.',
            'agency_id.required' => 'O órgão licitante é obrigatório.',
            'agency_id.exists' => 'O órgão licitante selecionado não existe.',
            'bidding_type.required' => 'O tipo de licitação é obrigatório.',
            'bidding_type.in' => 'O tipo de licitação selecionado é inválido.',
            'status.required' => 'O status da licitação é obrigatório.',
            'status.in' => 'O status selecionado é inválido.',
            'closing_date.after_or_equal' => 'A data de fechamento deve ser posterior ou igual à data de abertura.',
            'estimated_value.min' => 'O valor estimado não pode ser negativo.',

            'items.*.description.required' => 'A descrição do item é obrigatória.',
            'items.*.quantity.required' => 'A quantidade do item é obrigatória.',
            'items.*.quantity.min' => 'A quantidade do item deve ser maior que zero.',
            'items.*.estimated_unit_price.min' => 'O preço unitário estimado não pode ser negativo.',

            'attachments.*.max' => 'O arquivo não pode ser maior que 10MB.',
            'attachments.*.mimes' => 'O arquivo deve ser dos tipos: pdf, doc, docx, xls, xlsx, jpg, jpeg, png.',
        ];
    }
}
