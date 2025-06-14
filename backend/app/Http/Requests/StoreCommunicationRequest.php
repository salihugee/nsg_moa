<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunicationRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:sms,notification,alert'],
            'recipient_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:pending,sent,failed,delivered'],
            'sent_at' => ['nullable', 'date'],
            // For batch messaging
            'recipients' => ['sometimes', 'array'],
            'recipients.*' => ['exists:users,id'],
            // For targeted messaging
            'target_type' => ['sometimes', 'string', 'in:farmers,region,all'],
            'target_data' => ['required_with:target_type', 'array'],
            // For scheduling
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
            'target_type.in' => 'The target type must be farmers, region, or all',
            'schedule_for.after' => 'The scheduled time must be in the future',
        ];
    }
}
