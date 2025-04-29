<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentLikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:likes'],
            'data.relationships.comment.data.id' => ['sometimes', 'integer', 'exists:comments,id'],
        ];
    }
}
