<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('view-reports');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|before_or_equal:today',
            'agency_id' => 'nullable|exists:bidding_agencies,id',
            'bidding_type' => 'nullable|in:pregão,concorrência,tomada de preços,convite,leilão,concurso,outros',
            'format' => 'nullable|in:xlsx,pdf,csv',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Define valores padrão quando não fornecidos
        if (!$this->filled('start_date')) {
            $this->merge([
                'start_date' => Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'),
            ]);
        }

        if (!$this->filled('end_date')) {
            $this->merge([
                'end_date' => Carbon::now()->format('Y-m-d'),
            ]);
        }

        if (!$this->filled('format')) {
            $this->merge([
                'format' => 'xlsx',
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_date.required' => 'A data inicial é obrigatória.',
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            'start_date.before_or_equal' => 'A data inicial deve ser anterior ou igual à data final.',
            'end_date.required' => 'A data final é obrigatória.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.before_or_equal' => 'A data final não pode ser futura.',
            'agency_id.exists' => 'O órgão licitante selecionado não existe.',
            'bidding_type.in' => 'O tipo de licitação selecionado é inválido.',
            'format.in' => 'O formato selecionado é inválido.',
        ];
    }
}
