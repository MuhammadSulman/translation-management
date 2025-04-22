<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Translation Management API",
 *     version="1.0.0",
 *     description="API documentation for the Translation Management application.",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="API Support"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 *
 * @OA\Schema(
 *     schema="Language",
 *     type="object",
 *     title="Language",
 *     required={"id", "name", "code"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="English"),
 *     @OA\Property(property="code", type="string", example="en")
 * )
 *
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     title="Tag",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="greeting")
 * )
 *
 * @OA\Schema(
 *     schema="Translation",
 *     type="object",
 *     title="Translation",
 *     required={"id", "key", "value", "language"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="key", type="string", example="greeting.hello"),
 *     @OA\Property(property="value", type="string", example="Hello"),
 *     @OA\Property(property="language", ref="#/components/schemas/Language"),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Tag")
 *     )
 * )
 */
class ApiDocController
{
    // This class is only for OpenAPI annotations and will not be used directly.
}
