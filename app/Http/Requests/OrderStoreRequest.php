<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
        $rules = [
            'token' => [
                'required',
                'string'
            ]
        ];

        if (!auth()->check()) {
            $rules['email'] = [
                'required',
                'email',
            ];
        }

        return $rules;
    }
}