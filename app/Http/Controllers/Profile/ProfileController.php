<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Strava\Actions\Avatars\ChangeAvatarAction;
use Strava\Actions\Avatars\DeleteAvatarAction;
use Strava\Actions\Avatars\GetAvatarAction;
use Strava\Actions\Avatars\GetDefaultAvatarAction;
use Strava\Actions\Profile\UpdateProfileAction;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\ChangeAvatarRequest;
use Strava\Http\Requests\UpdateProfileRequest;
use Strava\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfileAction $updateProfileAction): UserResource
    {
        $validated = $request->validated();

        $user = $request->user();
        $updated = $updateProfileAction->execute($user, $validated);

        return UserResource::make($updated);
    }

    public function show(Request $request): UserResource
    {
        $user = $request->user();

        return UserResource::make($user);
    }

    public function changeAvatar(ChangeAvatarRequest $request, ChangeAvatarAction $changeAvatarAction): UserResource
    {
        $user = $request->user();

        $changeAvatarAction->execute(
            $request->file("avatar"),
            $user->id,
        );

        return UserResource::make($user);
    }

    public function getAvatar(int $userId, GetAvatarAction $getAvatarAction, GetDefaultAvatarAction $getDefaultAvatarAction): Response
    {
        $avatar = $getAvatarAction->execute($userId);

        if ($avatar) {
            return response($avatar)
                ->header("Content-Type", "image/png")
                ->header("Cache-Control", "public, max-age=31536000");
        }

        $defaultAvatar = $getDefaultAvatarAction->execute($userId);

        return response($defaultAvatar)
            ->header("Content-Type", "image/svg+xml")
            ->header("Cache-Control", "public, max-age=86400");
    }

    public function deleteAvatar(Request $request, DeleteAvatarAction $deleteProfileAction): UserResource
    {
        $user = $request->user();

        $deleteProfileAction->execute($user->id);

        return UserResource::make($user);
    }
}
