<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivestockRequest extends FormRequest
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
            'animal_type' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'health_status' => ['required', 'string', 'in:healthy,sick,quarantined,treated'],
            'location' => ['required', 'array'],
            'location.type' => ['required', 'string', 'in:Point'],
            'location.coordinates' => ['required', 'array', 'size:2'],
            'location.coordinates.0' => ['required', 'numeric'], // longitude
            'location.coordinates.1' => ['required', 'numeric'], // latitude
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
            'location.type.in' => 'The location must be of type Point',
            'location.coordinates.size' => 'Location coordinates must contain exactly longitude and latitude values',
        ];
    }
}
