<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreFarmerRequest",
 *     type="object",
 *     description="Farmer creation request",
 *     required={"registration_number", "full_name", "phone_number", "location_coordinates", "farm_size", "registration_date", "status"},
 *     @OA\Property(property="registration_number", type="string", maxLength=100),
 *     @OA\Property(property="full_name", type="string", maxLength=255),
 *     @OA\Property(property="phone_number", type="string", maxLength=20),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, nullable=true),
 *     @OA\Property(
 *         property="location_coordinates",
 *         type="object",
 *         @OA\Property(property="type", type="string", enum={"Point"}),
 *         @OA\Property(
 *             property="coordinates",
 *             type="array",
 *             minItems=2,
 *             maxItems=2,
 *             @OA\Items(type="number")
 *         )
 *     ),
 *     @OA\Property(property="farm_size", type="number", format="float", minimum=0),
 *     @OA\Property(property="registration_date", type="string", format="date"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "pending"})
 * )
 */
class StoreFarmerRequest extends FormRequest
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
            'registration_number' => ['required', 'string', 'max:100', 'unique:farmers'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'location_coordinates' => ['required', 'array'],
            'location_coordinates.type' => ['required', 'string', 'in:Point'],
            'location_coordinates.coordinates' => ['required', 'array', 'size:2'],
            'location_coordinates.coordinates.0' => ['required', 'numeric'], // longitude
            'location_coordinates.coordinates.1' => ['required', 'numeric'], // latitude
            'farm_size' => ['required', 'numeric', 'min:0'],
            'registration_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:active,inactive,pending'],
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
            'location_coordinates.type.in' => 'The location coordinates must be of type Point',
            'location_coordinates.coordinates.size' => 'Location coordinates must contain exactly longitude and latitude values',
        ];
    }
}
