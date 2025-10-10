<?php

namespace Strava\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\Auth\RegisterRequest;
use Strava\Models\User;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = new User($validated);
        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([], Response::HTTP_CREATED);
    }
}
