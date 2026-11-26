<?php

namespace App\Http\Requests\DogFacts;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fact' => ['nullable', 'string', 'max:255'],
            'createdFrom' => ['nullable', 'date_format:Y-m-d'],
            'createdUntil' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:createdFrom'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:'.config('pagination.max_size')],
        ];
    }
}
