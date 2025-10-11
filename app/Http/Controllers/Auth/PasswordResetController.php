<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\Auth\PasswordResetRequest;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function sendResetEmail(PasswordResetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Password::sendResetLink($validated);

        return response()->json([], Response::HTTP_OK);
    }
}
