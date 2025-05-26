<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreExpertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        \Log::info('authorize() called in StoreExpertRequest', ['user' => auth()->user()]);
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'profession' => 'required|string',
            'biography' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'experience' => 'required|string',
            'education' => 'required|string',
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
