<?php

namespace App\Http\Controllers\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API de ejemplo",
 *      description="Documentación API ejemplo con L5-Swagger",
 *      @OA\Contact(
 *          email="mpedrosa2110@gmail.com"
 *      )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use bearer token",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="sanctum"
 * )
 */
class OpenApiInfo
{
}
