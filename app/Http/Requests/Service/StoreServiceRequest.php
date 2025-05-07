<?php

namespace App\Http\Requests\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        \Log::info('authorize() called in StoreServiceRequest', ['user' => auth()->user()]);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Log::info('rules() called in StoreServiceRequest');
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
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
