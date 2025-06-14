<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:planned,ongoing,completed,suspended,cancelled'],
            'coverage_area' => ['sometimes', 'array'],
            'coverage_area.type' => ['required_with:coverage_area', 'string', 'in:Polygon,MultiPolygon'],
            'coverage_area.coordinates' => ['required_with:coverage_area', 'array'],
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
