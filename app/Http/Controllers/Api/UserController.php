<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * Generate random users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/users/generate",
     *     summary="Generate a list of random users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=true,
     *         description="Number of users to generate (max 100)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JSON file with generated users data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="firstName", type="string", example="John"),
     *                     @OA\Property(property="lastName", type="string", example="Doe"),
     *                     @OA\Property(property="birthDate", type="string", format="date", example="1990-01-15"),
     *                     @OA\Property(property="city", type="string", example="New York"),
     *                     @OA\Property(property="country", type="string", example="US"),
     *                     @OA\Property(property="avatar", type="string", format="url", example="https://example.com/avatar.jpg"),
     *                     @OA\Property(property="company", type="string", example="Acme Inc"),
     *                     @OA\Property(property="jobPosition", type="string", example="Developer"),
     *                     @OA\Property(property="mobile", type="string", example="+1234567890"),
     *                     @OA\Property(property="username", type="string", example="john.doe123"),
     *                     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                     @OA\Property(property="password", type="string", example="password123"),
     *                     @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function generate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $count = $request->input('count');
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $username = strtolower($firstName . '.' . $lastName . rand(1, 999));

            // Generate random password between 6-10 characters
            $password = Str::random(rand(6, 10));

            // Debug this
            \Log::info('Generating user: ' . $firstName . ' ' . $lastName);

            $users[] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'birthDate' => fake()->date('Y-m-d', '-18 years'),
                'city' => fake()->city(),
                'country' => fake()->countryCode(),
                'avatar' => fake()->imageUrl(200, 200, 'people'),
                'company' => fake()->company(),
                'jobPosition' => fake()->jobTitle(),
                'mobile' => fake()->phoneNumber(),
                'username' => $username,
                'email' => fake()->unique()->safeEmail(),
                'password' => $password,
                'role' => fake()->randomElement(['admin', 'user']),
            ];
        }

        // Create json file for download
        $json = json_encode($users, JSON_PRETTY_PRINT);
        $filename = 'users_' . time() . '.json';

        return response($json, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Import users from a JSON file
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/users/batch",
     *     summary="Import users from a JSON file",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="JSON file containing users data to import"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer", example=10),
     *             @OA\Property(property="imported", type="integer", example=8),
     *             @OA\Property(property="failed", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function batchImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $users = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON file'], 422);
        }

        $total = count($users);
        $imported = 0;
        $failed = 0;

        foreach ($users as $userData) {
            $validator = Validator::make($userData, [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'birthDate' => 'required|date',
                'city' => 'required|string|max:255',
                'country' => 'required|string|size:2',
                'avatar' => 'required|url',
                'company' => 'required|string|max:255',
                'jobPosition' => 'required|string|max:255',
                'mobile' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|max:10',
                'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            ]);

            if ($validator->fails()) {
                $failed++;
                continue;
            }

            try {
                User::create([
                    'firstName' => $userData['firstName'],
                    'lastName' => $userData['lastName'],
                    'birthDate' => $userData['birthDate'],
                    'city' => $userData['city'],
                    'country' => $userData['country'],
                    'avatar' => $userData['avatar'],
                    'company' => $userData['company'],
                    'jobPosition' => $userData['jobPosition'],
                    'mobile' => $userData['mobile'],
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'total' => $total,
            'imported' => $imported,
            'failed' => $failed,
        ]);
    }

    /**
     * Get current authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/users/me",
     *     summary="Get authenticated user profile",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Current user data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="firstName", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="john.doe123"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="country", type="string", example="US"),
     *             @OA\Property(property="avatar", type="string", format="url", example="https://example.com/avatar.jpg"),
     *             @OA\Property(property="company", type="string", example="Acme Inc"),
     *             @OA\Property(property="jobPosition", type="string", example="Developer"),
     *             @OA\Property(property="mobile", type="string", example="+1234567890"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Get user by username
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/users/{username}",
     *     summary="Get user profile by username",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="Username of the user to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="firstName", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="john.doe123"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="country", type="string", example="US"),
     *             @OA\Property(property="avatar", type="string", format="url", example="https://example.com/avatar.jpg"),
     *             @OA\Property(property="company", type="string", example="Acme Inc"),
     *             @OA\Property(property="jobPosition", type="string", example="Developer"),
     *             @OA\Property(property="mobile", type="string", example="+1234567890"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Only admins can view other users profiles"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        // Check if user is authorized to view the profile
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }
}
