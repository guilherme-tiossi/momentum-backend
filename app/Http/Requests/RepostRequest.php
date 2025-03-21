<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:reposts'],
            'data.relationships.post.data.id' => ['sometimes', 'integer', 'exists:posts,id'],
        ];
    }
}
