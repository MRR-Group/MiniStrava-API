<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Profile;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Strava\Actions\Profile\UpdateProfileAction;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\UpdateProfileRequest;
use Strava\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfileAction $updateProfileAction): JsonResponse
    {
        $validated = $request->validated();

        $user = request()->user();
        $updated = $updateProfileAction->execute($user, $validated);

        return response()->json(UserResource::make($updated), Response::HTTP_OK);
    }

    public function show(): JsonResponse
    {
        $user = request()->user();
        return response()->json(UserResource::make($user), Response::HTTP_OK);
    }
}
