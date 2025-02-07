<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'data.type' => ['required', 'string', 'in:users'],
            'data.attributes.name' => ['required', 'string'],
            'data.attributes.username' => ['required', 'string'],
            'data.attributes.email' => ['required', 'string', 'unique:users,email'],
            'data.attributes.password' => ['sometimes', 'string'],
            'data.attributes.pfp' => ['sometimes', 'integer'],
            'data.attributes.header' => ['sometimes', 'string'],
            'data.attributes.uses_default_pfp' => ['sometimes', 'boolean'],
            'data.attributes.uses_default_header' => ['sometimes', 'boolean'],
        ];
    }
}
