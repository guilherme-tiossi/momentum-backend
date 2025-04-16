<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecurrentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:recurrent_tasks'],
            'data.attributes.title' => ['required', 'string', 'max:255'],
            'data.attributes.description' => ['sometimes', 'string'],
            'data.attributes.recurrency_type' => ['required', 'string', 'in:daily,weekly,custom'],
            'data.attributes.days_of_week' => ['sometimes', 'array'],
            'data.attributes.days_of_week.*' => ['sometimes', 'integer'],
            'data.attributes.start_date' => ['sometimes', 'date'],
            'data.attributes.end_date' => ['sometimes', 'date'],

            'data.relationships.recurrent_task.data.id' => ['sometimes', 'integer', 'exists:recurrentTask,id'],
        ];
    }
}
