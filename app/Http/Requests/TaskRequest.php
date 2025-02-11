<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'data.type' => ['required', 'string', 'in:tasks'],
            'data.attributes.title' => ['required', 'string', 'max:255'],
            'data.attributes.description' => ['required', 'string'],
            'data.attributes.date' => ['required', 'date'],
            'data.attributes.finished' => ['sometimes', 'boolean'],
            'data.attributes.includes_weekend' => ['sometimes', 'boolean'],

            'data.relationships.task.data.id' => ['sometimes', 'integer', 'exists:tasks,id'],
        ];
    }
}
