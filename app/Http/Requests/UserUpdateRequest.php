<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:users'],
            'data.attributes.name' => ['required', 'string'],
            'data.attributes.username' => ['required', 'string'],
            'data.attributes.email' => ['required', 'string', 'STRING'],
            'data.attributes.bio' => ['sometimes', 'string'],
            'data.attributes.pfp' => ['sometimes', 'file', 'max:10240'],
            'data.attributes.header' => ['sometimes', 'string'],
            'data.attributes.location' => ['sometimes', 'string'],
            'data.attributes.uses_default_pfp' => ['sometimes', 'boolean'],
            'data.attributes.uses_default_header' => ['sometimes', 'boolean'],
        ];
    }
}
