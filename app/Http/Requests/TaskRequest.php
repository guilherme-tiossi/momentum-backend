<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:tasks'],
            'data.attributes.title' => ['required', 'string', 'max:255'],
            'data.attributes.description' => ['required', 'string'],
            'data.attributes.date' => ['required', 'date'],
            'data.attributes.finished' => ['sometimes', 'boolean'],

            'data.relationships.task.data.id' => ['sometimes', 'integer', 'exists:tasks,id'],
        ];
    }
}
