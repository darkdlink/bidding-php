<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScrapingConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-scraping');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'agency_id' => 'required|exists:bidding_agencies,id',
            'url' => 'required|url|max:255',
            'schedule' => 'required|string|max:50',
            'active' => 'boolean',

            // Seletores para scraping
            'selectors' => 'required|array',
            'selectors.bidding_list_selector' => 'required|string',
            'selectors.title_selector' => 'required|string',
            'selectors.external_id_selector' => 'nullable|string',
            'selectors.type_selector' => 'nullable|string',
            'selectors.modality_selector' => 'nullable|string',
            'selectors.publication_date_selector' => 'nullable|string',
            'selectors.opening_date_selector' => 'nullable|string',
            'selectors.closing_date_selector' => 'nullable|string',
            'selectors.document_url_selector' => 'nullable|string',
            'selectors.description_selector' => 'nullable|string',
            'selectors.estimated_value_selector' => 'nullable|string',
            'selectors.contact_email_selector' => 'nullable|string',
            'selectors.contact_phone_selector' => 'nullable|string',
            'selectors.items_list_selector' => 'nullable|string',
            'selectors.item_number_selector' => 'nullable|string',
            'selectors.item_description_selector' => 'nullable|string',
            'selectors.item_quantity_selector' => 'nullable|string',
            'selectors.item_unit_selector' => 'nullable|string',
            'selectors.item_price_selector' => 'nullable|string',
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
            'agency_id.required' => 'O órgão licitante é obrigatório.',
            'agency_id.exists' => 'O órgão licitante selecionado não existe.',
            'url.required' => 'A URL para scraping é obrigatória.',
            'url.url' => 'A URL fornecida não é válida.',
            'schedule.required' => 'O agendamento (cron) é obrigatório.',
            'selectors.required' => 'Os seletores para scraping são obrigatórios.',
            'selectors.bidding_list_selector.required' => 'O seletor da lista de licitações é obrigatório.',
            'selectors.title_selector.required' => 'O seletor do título da licitação é obrigatório.',
        ];
    }
}
