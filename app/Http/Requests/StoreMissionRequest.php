<?php

namespace App\Http\Requests;

use App\Models\Mission;
use App\Support\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMissionRequest extends FormRequest
{
    public const MAX_SKILLS = 8;

    public function authorize(): bool
    {
        return $this->user()->can('create', Mission::class);
    }

    protected function prepareForValidation(): void
    {
        // « 150 000 » ou « 150.000 » → 150000
        if (is_string($this->input('budget_fcfa'))) {
            $this->merge(['budget_fcfa' => preg_replace('/[^\d]/', '', $this->input('budget_fcfa'))]);
        }

        $this->merge(['remote' => $this->boolean('remote')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:10', 'max:120'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'budget_fcfa' => ['required', 'integer', 'min:'.Mission::MIN_BUDGET, 'max:50000000'],
            'remote' => ['boolean'],
            'country' => ['exclude_if:remote,true', 'required', 'string', Rule::in(Countries::codes())],
            'deadline' => ['nullable', 'date', 'after:today', 'before:+2 years'],
            'skills' => ['nullable', 'string', 'max:400'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $skills = $this->skillNames();

                if (count($skills) > self::MAX_SKILLS) {
                    $validator->errors()->add('skills', 'Indiquez au maximum '.self::MAX_SKILLS.' compétences.');
                }

                if (collect($skills)->contains(fn (string $skill) => mb_strlen($skill) > 40)) {
                    $validator->errors()->add('skills', 'Chaque compétence doit faire 40 caractères au maximum.');
                }
            },
        ];
    }

    /**
     * @return array{title: string, description: string, budget_fcfa: int, country: ?string, deadline: ?string}
     */
    public function missionData(): array
    {
        return [
            'title' => Str::squish($this->validated('title')),
            'description' => trim($this->validated('description')),
            'budget_fcfa' => (int) $this->validated('budget_fcfa'),
            'country' => $this->validated('remote') ? null : $this->validated('country'),
            'deadline' => $this->validated('deadline'),
        ];
    }

    /**
     * @return list<string>
     */
    public function skillNames(): array
    {
        return collect(explode(',', (string) $this->input('skills')))
            ->map(fn (string $skill) => Str::squish($skill))
            ->filter()
            ->unique(fn (string $skill) => Str::slug($skill))
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'budget_fcfa.min' => 'Le budget doit être d\'au moins 5 000 FCFA.',
            'budget_fcfa.integer' => 'Indiquez le budget en chiffres, par exemple 150000.',
            'country.required' => 'Choisissez un pays, ou cochez « Télétravail ».',
            'deadline.after' => 'L\'échéance doit être une date future.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'description' => 'description',
            'budget_fcfa' => 'budget',
            'deadline' => 'échéance',
        ];
    }
}
