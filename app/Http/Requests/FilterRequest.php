<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'string|nullable',
            'category' => 'string|nullable',
            'rating' => 'numeric|nullable',
            'isAFree' => 'boolean|nullable',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('isAFree')) {
            $this->merge([
                'isAFree' => filter_var($this->input('isAFree'), FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
