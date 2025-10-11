<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Auth;

use Strava\Actions\Auth\ResetPasswordAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\Auth\ForgotPasswordRequest;
use Strava\Http\Requests\Auth\ResetPasswordRequest;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends Controller
{
    public function sendResetEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Password::sendResetLink($validated);

        return response()->json([], Response::HTTP_OK);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $resetPasswordAction): JsonResponse
    {
        $validated = $request->validated();
        $success = $resetPasswordAction->execute($validated);

        return $success
            ? response()->json([], Response::HTTP_OK)
            : response()->json([], Response::HTTP_BAD_REQUEST);
    }
}
