<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WebhookAgentIntgr extends FormRequest
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
            'settings.ms_token' => 'required|string|min:1|max:42',
            'settings.lineName' => 'required|string|min:1|max:255',
            'settings.is_messenger' => 'required|boolean',
            'settings.messenger_attribute_id' => 'required|uuid',
            'webhook.meta.messenger' => 'required|string',
            'webhook.data' => 'required|array',
            'webhook.data.*.type' => 'required|string|min:1|max:64',
            'webhook.data.*.text' => 'required|string|min:1|max:8192',
            'webhook.data.*.fromMe' => 'required|boolean',
            'webhook.data.*.chat.phone' => 'nullable|string|min:1|max:12',
            'webhook.data.*.chat.username' => 'nullable|string|min:1|max:255',
            'webhook.data.*.chat.name' => 'nullable|string|min:1|max:255',
            'webhook.data.*.chat.id' => 'required|string|min:1|max:255',
            'webhook.data.*.chat.email' => 'nullable|email:filter|min:1|max:255',
        ];
    }

    public function messages()
    {
        return [
            'settings.ms_token.required' => 'токен обязателен для заполнения',
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

//
/*{
    "settings": {
        "ms_token" : "1",
        "lineName": "0",
        "is_messenger" : "1",
        "messenger_attribute_id": "1dd5bd55-d141-11ec-0a80-055600047495"
    },
    "webhook":{
        "meta":{
            "lineId" : 36651,
            "messenger": "telegram"
        },
        "data":[
            {
                "type": "text",
                "text": "123qwerty",
                "fromMe": "true",
                "chat": {
                    "phone": null,
                    "username": null,
                    "name": null,
                    "id": "123",
                    "email": null
                }
            }
        ]

    }
        
}*/
