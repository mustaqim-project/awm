<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'is_all_day' => ['nullable', 'boolean'],
            'color_tag' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
            'sync_google' => ['nullable', 'boolean'],
            'reminders' => ['nullable', 'array'],
            'reminders.*.channel' => ['required', 'string', 'in:whatsapp,email,in_app'],
            'reminders.*.minutes_before' => ['required', 'integer', 'min:0'],
            'reminders.*.recipient_phone' => ['nullable', 'string', 'max:50'],
            'reminders.*.recipient_email' => ['nullable', 'email', 'max:150'],
        ];
    }
}
