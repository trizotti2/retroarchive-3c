<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'required', 'string', 'max:100'],
            'category' => ['sometimes', 'required', 'string', 'max:255'],
            'sort' => ['sometimes', 'required', 'in:latest,price_asc,price_desc'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
        ];
    }
}