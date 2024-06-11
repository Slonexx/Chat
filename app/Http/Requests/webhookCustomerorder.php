<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class webhookCustomerorder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            '*.phone' => 'nullable|string|min:1|max:12',
            '*.username' => 'nullable|string|min:1|max:255',
            '*.name' => 'nullable|string|min:1|max:255',
            '*.id' => 'required|string|min:1|max:255',
            '*.email' => 'nullable|email:filter|min:1|max:255',
            '*.unreadMessages' => 'required|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Ошибка валидации',
            'errors' => $errors
        ], 422));
    }
}
