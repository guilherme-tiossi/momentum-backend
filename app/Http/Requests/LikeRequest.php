<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:likes'],
            'data.relationships.post.data.id' => ['sometimes', 'integer', 'exists:posts,id'],
        ];
    }
}
