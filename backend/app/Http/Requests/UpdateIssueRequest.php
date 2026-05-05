<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:5', 'max:255'],
            'description' => ['sometimes', 'string', 'min:20'],
            'priority' => ['sometimes', 'in:low,medium,high,critical'],
            'category' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'in:open,in_progress,resolved,closed'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
