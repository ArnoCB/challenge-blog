<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user with name and password. The name should be a valid email address.
     *
     * @param Request $request
     * @return JsonResponse
     * {
     *  "access_token": xxx
     *  "token_type": "bearer",
     *  "expires_in": 3600
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all() ,[
            'name' => 'required|email',
            'password' => 'required|max:65535',
        ]);

        if ($validator->fails()) {

            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->name,
            'password' => $request->password,
        ]);

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * Check if name and password combination is valid.
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        $credentials = request(['name', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @param $token
     * @return JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
