<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:comments'],
            'data.attributes.text' => ['required', 'string'],
            'data.relationships.post.data.id' => ['sometimes', 'integer', 'exists:posts,id']
        ];
    }
}
