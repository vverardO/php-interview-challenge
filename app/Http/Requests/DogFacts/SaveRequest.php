<?php

namespace App\Http\Requests\DogFacts;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fact' => ['required', 'string', 'max:255'],
        ];
    }
}
