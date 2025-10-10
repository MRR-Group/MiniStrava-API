<?php

namespace Strava\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\Auth\LoginRequest;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        if(!Auth::attempt($credentials)) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            "token" => $token,
            "user_id" => $user->id,
        ], Response::HTTP_OK);

    }
}
