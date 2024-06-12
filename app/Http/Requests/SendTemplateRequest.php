<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SendTemplateRequest extends FormRequest
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
        $allowedTypes = [
            "customerorder",
            "demand", 
            "salesreturn",
            "invoiceout",
            "counterparty",
        ];
        return [
            '*.accountId' => 'required|uuid',
            '*.type' => ['required', Rule::in($allowedTypes)],
            '*.href' => 'required|url',
            '*.employeeId' => 'nullable|uuid',
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
