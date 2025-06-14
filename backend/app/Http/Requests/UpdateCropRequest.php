<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCropRequest extends FormRequest
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
            'farm_id' => ['required', 'exists:farms,id'],
            'crop_type' => ['required', 'string', 'max:100'],
            'planting_date' => ['required', 'date'],
            'expected_harvest_date' => ['required', 'date', 'after:planting_date'],
            'actual_harvest_date' => ['nullable', 'date', 'after:planting_date'],
            'yield_quantity' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:planted,growing,ready_for_harvest,harvested,failed'],
        ];
    }
}
