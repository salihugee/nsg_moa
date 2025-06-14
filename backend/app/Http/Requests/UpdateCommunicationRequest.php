<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will add proper authorization later
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:sms,notification,alert'],
            'recipient_id' => ['sometimes', 'exists:users,id'],
            'message' => ['sometimes', 'string', 'max:1000'],
            'status' => ['sometimes', 'string', 'in:pending,sent,failed,delivered'],
            'sent_at' => ['nullable', 'date'],
            // For rescheduling
            'schedule_for' => ['sometimes', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'type.in' => 'The communication type must be sms, notification, or alert',
            'status.in' => 'The status must be pending, sent, failed, or delivered',
            'schedule_for.after' => 'The scheduled time must be in the future',
        ];
    }
}
