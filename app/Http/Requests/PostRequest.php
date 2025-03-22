<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:posts'],
            'data.attributes.text' => ['required', 'string'],
            'data.attributes.attachments' => ['sometimes', 'nullable', 'array', 'required_unless:data.attributes.attachments,null'],
            'data.attributes.attachments.*' => ['file', 'max:10240'],
        ];
    }
}
