<?php

namespace Strava\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Strava\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        $token->delete();

        return response()->json([], Response::HTTP_OK);
    }
}
