<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('mission'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stars = ['required', 'integer', 'between:1,5'];

        return [
            'rating' => $stars,
            'communication' => $stars,
            'quality' => $stars,
            'timeliness' => $stars,
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Choisissez une note globale, de 1 à 5 étoiles.',
            '*.required' => 'Choisissez une note de 1 à 5 étoiles.',
            '*.between' => 'La note va de 1 à 5 étoiles.',
            'comment.max' => 'Le commentaire fait au plus 1 000 caractères.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['rating' => 'note globale'] + Review::CRITERIA;
    }
}
