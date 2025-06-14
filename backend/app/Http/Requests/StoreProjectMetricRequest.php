<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMetricRequest extends FormRequest
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
            'project_id' => ['required', 'exists:projects,id'],
            'metric_name' => ['required', 'string', 'max:255'],
            'target_value' => ['required', 'numeric'],
            'current_value' => ['nullable', 'numeric'],
            'last_updated' => ['nullable', 'date'],
        ];
    }
}
