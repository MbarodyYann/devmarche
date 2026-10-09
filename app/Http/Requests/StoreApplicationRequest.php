<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('apply', $this->route('mission'));
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('proposed_fcfa'))) {
            $this->merge(['proposed_fcfa' => preg_replace('/[^\d]/', '', $this->input('proposed_fcfa'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'proposed_fcfa' => ['required', 'integer', 'min:1000', 'max:50000000'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_days.min' => 'Indiquez un délai d\'au moins 1 jour.',
            'delivery_days.max' => 'Le délai ne peut pas dépasser 180 jours.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => 'message',
            'proposed_fcfa' => 'prix proposé',
            'delivery_days' => 'délai de livraison',
        ];
    }
}
