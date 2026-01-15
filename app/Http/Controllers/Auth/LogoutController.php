<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Strava\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard("web")->logout();

            if (request()->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->forget("tfa_passed");
            }
        }

        return response()->json([], Response::HTTP_OK);
    }
}
