<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Profile;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Strava\Http\Controllers\Controller;
use Strava\Http\Resources\UserResource;
use Strava\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ProfilesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = User::query()->select()->paginate(10);

        return UserResource::collection($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::query()->find($id);

        if ($user === null) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        return response()->json(UserResource::make($user), Response::HTTP_OK);
    }
}
