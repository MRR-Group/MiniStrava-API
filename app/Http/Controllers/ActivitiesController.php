<?php

declare(strict_types=1);

namespace Strava\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Strava\Actions\Activities\BuildGpxFileAction;
use Strava\Actions\Activities\CreateActivityAction;
use Strava\Actions\Activities\GetActivityPhotoAction;
use Strava\Actions\Activities\GetActivitySummaryAction;
use Strava\Actions\Activities\StoreActivityGpsPointsAction;
use Strava\Actions\Activities\StoreActivityPhotoAction;
use Strava\Helpers\SortHelper;
use Strava\Http\Requests\StoreActivityRequest;
use Strava\Http\Resources\ActivityResource;
use Strava\Models\Activity;

class ActivitiesController extends Controller
{
    public function index(Request $request, SortHelper $sortHelper): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Activity::query()->where("user_id", $user->id);

        $query = $sortHelper->sort($query, ["activity_type", "created_at", "distance_m", "duration_s", "id"], []);
        $query = $this->filterDate($query, $request);
        $query = $this->filterType($query, $request);
        $query = $this->filterDistance($query, $request);
        $query = $this->filterDuration($query, $request);

        $activities = $query->get();

        return ActivityResource::collection($activities);
    }

    public function show(int $id, Request $request): ActivityResource
    {
        $user = $request->user();

        $activity = Activity::query()
            ->where("user_id", $user->id)
            ->findOrFail($id);

        return ActivityResource::make(
            $activity->load("gpsPoints"),
        );
    }

    public function store(
        StoreActivityRequest $request,
        CreateActivityAction $createActivityAction,
        StoreActivityPhotoAction $storeActivityPhotoAction,
        StoreActivityGpsPointsAction $storeActivityGpsPointsAction,
    ): ActivityResource {
        $validated = $request->validated();
        $user = $request->user();
        $photo = $request->file("photo");

        $activity = DB::transaction(function () use (
            $createActivityAction,
            $storeActivityPhotoAction,
            $storeActivityGpsPointsAction,
            $user,
            $validated,
            $photo
        ) {
            $activity = $createActivityAction->execute($user->id, $validated);

            if (!empty($validated["gps_points"] ?? null)) {
                $storeActivityGpsPointsAction->execute($activity->id, $validated["gps_points"]);
            }

            if ($photo) {
                $storeActivityPhotoAction->execute($photo, $activity->id);
            }

            return $activity;
        });

        return ActivityResource::make(
            $activity->load("gpsPoints"),
        );
    }

    public function getSummary(Activity $activity, Request $request, GetActivitySummaryAction $getActivitySummaryAction)
    {
        $user = $request->user();

        if (!$user->has_premium) {
            abort(402);
        }
        $summary = $getActivitySummaryAction->execute($user, $activity);

        return response()->json([
            "summary" => $summary,
        ]);
    }

    public function exportGpx(int $id, Request $request, BuildGpxFileAction $buildGpxFile): Response
    {
        $activity = Activity::query()
            ->where("user_id", $request->user()->id)
            ->with(["gpsPoints" => fn($q) => $q->orderBy("timestamp")])
            ->findOrFail($id);

        $gpxFile = $buildGpxFile->execute($activity);

        $xml = $gpxFile->toXML()->saveXML();

        $activityDateTime = $activity->started_at->format("Y-m-d_H:i:s");

        return response()->make($xml, 200, [
            "Content-Type" => "application/gpx+xml; charset=UTF-8",
            "Content-Disposition" => 'attachment; filename="activity-' . $activityDateTime . '.gpx"',
        ]);
    }

    public function getPhoto(int $id, Request $request, GetActivityPhotoAction $getActivityPhotoAction): Response
    {
        $user = $request->user();
        $activity = Activity::query()->findOrFail($id);

        if ($activity->user_id !== $user->id) {
            abort(403);
        }

        $photo = $getActivityPhotoAction->execute($id);

        if ($photo) {
            return response($photo)
                ->header("Content-Type", "image/png")
                ->header("Cache-Control", "max-age=31536000, public");
        }

        return response()->noContent();
    }

    private function filterType(Builder $query, Request $request): Builder
    {
        $type = $request->query("activity_type");

        if ($type === null) {
            return $query;
        }

        return $query->where(fn(Builder $q) => $q->where("activity_type", "=", $type));
    }

    private function filterDate(Builder $query, Request $request): Builder
    {
        $from = $request->query("date_from");
        $to = $request->query("date_to");

        if ($from === null && $to === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($from, $to): void {
            if ($from !== null) {
                $q->whereDate("created_at", ">=", $from);
            }

            if ($to !== null) {
                $q->whereDate("created_at", "<=", $to);
            }
        });
    }

    private function filterDistance(Builder $query, Request $request): Builder
    {
        $min = $request->query("distance_min");
        $max = $request->query("distance_max");

        if ($min === null && $max === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($min, $max): void {
            if ($min !== null) {
                $q->where("distance_m", ">=", $min);
            }

            if ($max !== null) {
                $q->where("distance_m", "<=", $max);
            }
        });
    }

    private function filterDuration(Builder $query, Request $request): Builder
    {
        $min = $request->query("duration_min");
        $max = $request->query("duration_max");

        if ($min === null && $max === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($min, $max): void {
            if ($min !== null) {
                $q->where("duration_s", ">=", $min);
            }

            if ($max !== null) {
                $q->where("duration_s", "<=", $max);
            }
        });
    }
}
