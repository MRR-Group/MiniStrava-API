<?php

declare(strict_types=1);

namespace Strava\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Strava\Actions\Activities\CreateActivityAction;
use Strava\Actions\Activities\GetActivityPhotoAction;
use Strava\Actions\Activities\ListActivitiesAction;
use Strava\Http\Requests\StoreActivityRequest;
use Strava\Http\Resources\ActivityResource;
use Strava\Models\Activity;

class ActivitiesController extends Controller
{
    public function index(Request $request, ListActivitiesAction $listActivitiesAction)
    {
        $user = $request->user();

        $activities = $listActivitiesAction->execute($user->id);

        return ActivityResource::collection($activities);
    }

    public function show(int $id, Request $request): ActivityResource
    {
        $user = $request->user();

        $activity = Activity::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return new ActivityResource($activity);
    }

    public function store(StoreActivityRequest $request, CreateActivityAction $createActivityAction): ActivityResource
    {
        $validated = $request->validated();
        $user = $request->user();
        $photo = $request->file("photo");

        $activity = $createActivityAction->execute($user->id, $validated);

        $photo?->storeAs("", "activity_" . $activity->id . ".png", "activityPhotos");

        return ActivityResource::make($activity);
    }

    public function getPhoto(int $id, GetActivityPhotoAction $getActivityPhotoAction): Response
    {
        $photo = $getActivityPhotoAction->execute($id);

        if ($photo) {
            return response($photo)
                ->header("Content-Type", "image/png")
                ->header("Cache-Control", "max-age=31536000, public");
        }

        return response()->noContent();
    }
}
