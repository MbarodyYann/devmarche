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
        ];
    }
}
