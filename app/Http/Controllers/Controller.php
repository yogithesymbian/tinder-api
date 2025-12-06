<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Tinder API",
 *     version="1.0.0",
 *     description="API documentation for Tinder API project (Technically Assignment). This API provides authentication endpoints using Laravel Sanctum.",
 *
 *     @OA\Contact(
 *         email="support@tinder-api.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format (Bearer <token>)"
 * )
 */
abstract class Controller
{
    //
}
