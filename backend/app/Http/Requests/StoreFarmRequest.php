<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmRequest extends FormRequest
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
            'farmer_id' => ['required', 'exists:farmers,id'],
            'boundaries' => ['required', 'array'],
            'boundaries.type' => ['required', 'string', 'in:Polygon'],
            'boundaries.coordinates' => ['required', 'array'],
            'boundaries.coordinates.*' => ['required', 'array'],
            'boundaries.coordinates.*.*' => ['required', 'array', 'size:2'],
            'boundaries.coordinates.*.*.0' => ['required', 'numeric'], // longitude
            'boundaries.coordinates.*.*.1' => ['required', 'numeric'], // latitude
            'size_hectares' => ['required', 'numeric', 'min:0'],
            'soil_type' => ['required', 'string', 'max:100'],
            'water_source' => ['required', 'string', 'max:100'],
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
            'boundaries.type.in' => 'The boundaries must be of type Polygon',
            'boundaries.coordinates.*.*.size' => 'Each coordinate pair must contain exactly longitude and latitude values',
        ];
    }
}
