<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:planned,ongoing,completed,suspended,cancelled'],
            'coverage_area' => ['required', 'array'],
            'coverage_area.type' => ['required', 'string', 'in:Polygon,MultiPolygon'],
            'coverage_area.coordinates' => ['required', 'array'],
            'metrics' => ['sometimes', 'array'],
            'metrics.*.metric_name' => ['required_with:metrics', 'string', 'max:255'],
            'metrics.*.target_value' => ['required_with:metrics', 'numeric'],
            'metrics.*.current_value' => ['sometimes', 'numeric'],
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
            'coverage_area.type.in' => 'The coverage area must be of type Polygon or MultiPolygon',
            'end_date.after' => 'The end date must be after the start date',
        ];
    }
}
