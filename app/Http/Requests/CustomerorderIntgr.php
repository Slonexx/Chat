<?php

namespace App\Http\Requests;

use App\Rules\UuidOrNull;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerorderIntgr extends FormRequest
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
            'ms_token' => 'required|string|min:1|max:42',
            'org' => 'required|array',
            'org.*.accessToken' => 'required|string|min:1|max:64',
            'org.*.lineId' => 'required|string|min:1|max:10',
            'org.*.lineName' => 'required|string|min:1|max:128',
            'messengerAttributes' => 'required|array',
            'messengerAttributes.*.name' => 'required|string|min:1|max:32',
            'messengerAttributes.*.attribute_id' => 'required|uuid',
            'lid.responsible' => 'required|in:0,1,2',
            'lid.responsible_uuid' => ['required', new UuidOrNull],
            'lid.is_activity_order' => 'required|boolean',
            'lid.organization' => 'required|uuid',
            'lid.organization_account' => 'required|uuid',
            'lid.sales_channel_uid' => ['required', new UuidOrNull],
            'lid.project_uid' => ['required', new UuidOrNull],
            'lid.states' => ['required', new UuidOrNull],
            'lid.tasks' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'ms_token' => 'токен обязателен для заполнения',
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
