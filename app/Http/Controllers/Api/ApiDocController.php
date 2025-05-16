<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Users Generator API",
 *     version="1.0.0",
 *     description="API for generating and managing users",
 *     @OA\Contact(
 *         email="amonelnathan@gmail.com",
 *         name="API Support"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:9090/api",
 *     description="Local API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiDocController extends Controller
{
    /**
     * Show API documentation
     *
     * @return JsonResponse
     */
    public function documentation(): JsonResponse
    {
        $openapi = \OpenApi\Generator::scan([
            app_path('Http/Controllers/Api'),
            app_path('Models'),
        ]);

        return response()->json($openapi);
    }
}
