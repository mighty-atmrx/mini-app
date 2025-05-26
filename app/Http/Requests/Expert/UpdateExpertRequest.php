<?php

namespace App\Http\Requests\Expert;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UpdateExpertRequest extends FormRequest
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
        \Log::info('rules() called in StoreExpertRequest');
        return [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'profession' => 'nullable|string',
            'biography' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'experience' => 'nullable|string',
            'education' => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::error('Validation failed', [
            'errors' => $validator->errors()->all(),
        ]);

        throw new HttpResponseException(
            response()->json('Ошибка валидации данных.', Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
