<?php

namespace App\OpenApi;

/**
 * Class containing OpenAPI schema definitions.
 */
class Schemas {
    /**
     * @OA\Schema(
 *     schema="Farmer",
 *     required={"name", "registration_number", "phone"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="registration_number", type="string", maxLength=20),
 *     @OA\Property(property="phone", type="string", maxLength=20),
 *     @OA\Property(property="address", type="string", maxLength=255),
 *     @OA\Property(property="location_coordinates", type="object",
 *         @OA\Property(property="type", type="string", example="Point"),
 *         @OA\Property(property="coordinates", type="array", @OA\Items(type="number"), example={8.4927, 8.5227})
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="StoreFarmerRequest",
 *     required={"name", "registration_number", "phone"},
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="registration_number", type="string", maxLength=20),
 *     @OA\Property(property="phone", type="string", maxLength=20),
 *     @OA\Property(property="address", type="string", maxLength=255),
 *     @OA\Property(property="location_coordinates", type="object",
 *         @OA\Property(property="type", type="string", example="Point"),
 *         @OA\Property(property="coordinates", type="array", @OA\Items(type="number"), example={8.4927, 8.5227})
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateFarmerRequest",
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="phone", type="string", maxLength=20),
 *     @OA\Property(property="address", type="string", maxLength=255),
 *     @OA\Property(property="location_coordinates", type="object",
 *         @OA\Property(property="type", type="string", example="Point"),
 *         @OA\Property(property="coordinates", type="array", @OA\Items(type="number"), example={8.4927, 8.5227})
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
}
