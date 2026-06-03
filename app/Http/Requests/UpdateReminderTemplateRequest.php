<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'templates' => ['required', 'array'],
            'templates.*.id' => ['required', 'integer', 'exists:reminder_templates,id'],
            'templates.*.subject' => ['nullable', 'string', 'max:255'],
            'templates.*.body' => ['required', 'string'],
            'templates.*.is_active' => ['required', 'boolean'],
        ];
    }
}
