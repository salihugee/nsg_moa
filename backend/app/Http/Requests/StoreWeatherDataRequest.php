<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeatherDataRequest extends FormRequest
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
            'location' => ['required', 'array'],
            'location.type' => ['required', 'string', 'in:Point'],
            'location.coordinates' => ['required', 'array', 'size:2'],
            'location.coordinates.0' => ['required', 'numeric'], // longitude
            'location.coordinates.1' => ['required', 'numeric'], // latitude
            'temperature' => ['required', 'numeric', 'between:-50,60'], // reasonable temperature range in Celsius
            'rainfall' => ['required', 'numeric', 'min:0', 'max:1000'], // in millimeters
            'humidity' => ['required', 'numeric', 'between:0,100'], // percentage
            'wind_speed' => ['required', 'numeric', 'min:0', 'max:200'], // in km/h
            'recorded_at' => ['required', 'date'],
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
            'temperature.between' => 'The temperature must be between -50°C and 60°C',
            'rainfall.max' => 'The rainfall value seems unusually high',
            'humidity.between' => 'Humidity must be between 0% and 100%',
            'wind_speed.max' => 'The wind speed value seems unusually high',
        ];
    }
}
