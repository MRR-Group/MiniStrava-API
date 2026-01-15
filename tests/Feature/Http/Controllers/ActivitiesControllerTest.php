<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Strava\Models\Activity;
use Strava\Models\GpsPoint;
use Strava\Models\User;
use Tests\TestCase;

class ActivitiesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexRequiresAuthentication(): void
    {
        $res = $this->getJson(route("activities.index"));
        $res->assertUnauthorized();
    }

    public function testIndexReturnsOnlyCurrentUserActivities(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);
        $other = User::factory()->create(["email" => "u2@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $user->id,
                "title" => "Old",
                "notes" => null,
                "duration_s" => 100,
                "distance_m" => 1000,
                "activity_type" => "run",
                "started_at" => Carbon::parse("2026-01-02 10:00:00"),
                "created_at" => Carbon::parse("2026-01-01 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-01 10:00:00"),
            ],
            [
                "user_id" => $user->id,
                "title" => "New",
                "notes" => null,
                "duration_s" => 200,
                "distance_m" => 2000,
                "activity_type" => "run",
                "started_at" => Carbon::parse("2026-01-02 10:00:00"),
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $other->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 999,
                "distance_m" => 9999,
                "activity_type" => "other",
                "started_at" => Carbon::parse("2026-01-02 10:00:00"),
                "created_at" => Carbon::parse("2026-01-03 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 10:00:00"),
            ],
        ]);

        $res = $this->actingAs($user)->getJson(route("activities.index"));

        $res->assertOk();
        $res->assertJsonStructure([
            "data" => [
                ["id", "title", "notes", "activity_type", "duration_s", "distance_m", "photo", "started_at"],
            ],
        ]);

        $data = $res->json("data");

        $this->assertCount(2, $data);

        $titles = array_column($data, "title");
        $this->assertContains("New", $titles);
        $this->assertContains("Old", $titles);
        $this->assertNotContains("Other", $titles);

        $this->assertArrayNotHasKey("gps_points", $data[0]);
        $this->assertArrayNotHasKey("gps_points", $data[1]);
    }

    public function testIndexCanSortByCreatedAtDesc(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $user->id,
                "title" => "Old",
                "notes" => null,
                "duration_s" => 100,
                "distance_m" => 1000,
                "activity_type" => "run",
                "started_at" => Carbon::parse("2026-01-02 10:00:00"),
                "created_at" => Carbon::parse("2026-01-01 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-01 10:00:00"),
            ],
            [
                "user_id" => $user->id,
                "title" => "New",
                "notes" => null,
                "duration_s" => 200,
                "distance_m" => 2000,
                "activity_type" => "run",
                "started_at" => Carbon::parse("2026-01-02 10:00:00"),
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
        ]);

        $res = $this->actingAs($user)->getJson(route("activities.index", [
            "sort" => "created_at",
            "order" => "desc",
        ]));

        $res->assertOk();

        $data = $res->json("data");

        $this->assertSame("New", $data[0]["title"]);
        $this->assertSame("Old", $data[1]["title"]);
    }

    public function testShowRequiresAuthentication(): void
    {
        $res = $this->getJson(route("activities.show", ["id" => 1]));
        $res->assertUnauthorized();
    }

    public function testShowReturnsOnlyOwnActivity(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);
        $other = User::factory()->create(["email" => "u2@gmail.com"]);

        $a1 = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Mine",
            "notes" => null,
            "duration_s" => 100,
            "distance_m" => 1000,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00"),
        ]);

        $a2 = Activity::query()->create([
            "user_id" => $other->id,
            "title" => "NotMine",
            "notes" => null,
            "duration_s" => 100,
            "distance_m" => 1000,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00"),
        ]);

        $res = $this->actingAs($user)->getJson(route("activities.show", ["id" => $a1->id]));
        $res->assertOk();
        $res->assertJsonPath("data.id", $a1->id);
        $res->assertJsonStructure([
            "data" => ["id", "title", "notes", "activity_type", "duration_s", "distance_m", "photo", "started_at", "gps_points"],
        ]);
        $this->assertIsArray($res->json("data.gps_points"));

        $res = $this->actingAs($user)->getJson(route("activities.show", ["id" => $a2->id]));
        $res->assertNotFound();
    }

    public function testStoreRequiresAuthentication(): void
    {
        $res = $this->postJson(route("activities.store"), []);
        $res->assertUnauthorized();
    }

    public function testStoreValidatesPayload(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $res = $this->actingAs($user)->postJson(route("activities.store"), []);

        $res->assertUnprocessable();
        $res->assertJsonValidationErrors([
            "title",
            "duration_s",
            "distance_m",
            "activity_type",
            "started_at",
        ]);
    }

    public function testStoreCreatesActivityWithoutPhotoAndReturnsResource(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $payload = [
            "title" => "Run",
            "notes" => "Note",
            "duration_s" => 120,
            "distance_m" => 1500,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
        ];

        $res = $this->actingAs($user)->postJson(route("activities.store"), $payload);

        $res->assertCreated();
        $res->assertJsonStructure([
            "data" => ["id", "title", "notes", "activity_type", "duration_s", "distance_m", "photo", "started_at", "gps_points"],
        ]);

        $this->assertSame([], $res->json("data.gps_points"));

        $this->assertDatabaseHas("activities", [
            "user_id" => $user->id,
            "title" => "Run",
            "duration_s" => 120,
            "distance_m" => 1500,
        ]);
    }

    public function testStoreSavesGpsPointsWhenProvided(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $payload = [
            "title" => "Run",
            "notes" => "Note",
            "duration_s" => 120,
            "distance_m" => 1500,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
            "gps_points" => [
                [
                    "lat" => 52.1,
                    "lng" => 21.2,
                    "alt_m" => 100.5,
                    "accuracy_m" => 3.4,
                    "timestamp" => 1700000000,
                ],
                [
                    "lat" => 52.2,
                    "lng" => 21.3,
                    "timestamp" => 1700000001,
                ],
            ],
        ];

        $res = $this->actingAs($user)->postJson(route("activities.store"), $payload);

        $res->assertCreated();

        $activityId = (int)$res->json("data.id");

        $this->assertDatabaseCount("gps_points", 2);

        $this->assertDatabaseHas("gps_points", [
            "activity_id" => $activityId,
            "lat" => 52.1,
            "lng" => 21.2,
            "timestamp" => 1700000000,
        ]);

        $this->assertDatabaseHas("gps_points", [
            "activity_id" => $activityId,
            "lat" => 52.2,
            "lng" => 21.3,
            "timestamp" => 1700000001,
        ]);
    }

    public function testStoreRejectsInvalidGpsPoints(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $res = $this->actingAs($user)->postJson(route("activities.store"), [
            "title" => "Run",
            "notes" => null,
            "duration_s" => 1,
            "distance_m" => 1,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
            "gps_points" => [
                [
                    "lng" => 21.2,
                    "timestamp" => 1700000000,
                ],
            ],
        ]);

        $res->assertUnprocessable();
        $res->assertJsonValidationErrors([
            "gps_points.0.lat",
        ]);
    }

    public function testStoreRejectsNegativeGpsTimestamp(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $res = $this->actingAs($user)->postJson(route("activities.store"), [
            "title" => "Run",
            "notes" => null,
            "duration_s" => 1,
            "distance_m" => 1,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
            "gps_points" => [
                [
                    "lat" => 52.1,
                    "lng" => 21.2,
                    "timestamp" => -1,
                ],
            ],
        ]);

        $res->assertUnprocessable();
        $res->assertJsonValidationErrors([
            "gps_points.0.timestamp",
        ]);
    }

    public function testStoreRejectsNonPngPhoto(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $file = UploadedFile::fake()->create("x.jpg", 10, "image/jpeg");

        $res = $this->actingAs($user)->postJson(route("activities.store"), [
            "title" => "Run",
            "notes" => null,
            "duration_s" => 1,
            "distance_m" => 1,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
            "photo" => $file,
        ]);

        $res->assertUnprocessable();
        $res->assertJsonValidationErrors(["photo"]);
    }

    public function testStoreSavesPngPhotoOnActivityPhotosDiskWhenProvided(): void
    {
        Storage::fake("activityPhotos");

        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $file = UploadedFile::fake()->create("photo.png", 10, "image/png");

        $res = $this->actingAs($user)->postJson(route("activities.store"), [
            "title" => "Run",
            "notes" => null,
            "duration_s" => 120,
            "distance_m" => 1500,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00")->toISOString(),
            "photo" => $file,
        ]);

        $res->assertCreated();

        $activityId = (int)$res->json("data.id");

        Storage::disk("activityPhotos")
            ->assertExists("activity_" . $activityId . ".png");
    }

    public function testExportGpxRequiresAuthentication(): void
    {
        $res = $this->getJson(route("activities.export.gpx", ["id" => 1]));
        $res->assertUnauthorized();
    }

    public function testExportGpxReturnsGpxXmlWithHeaders(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => 100,
            "distance_m" => 1000,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-01 10:00:00"),
        ]);

        GpsPoint::query()->insert([
            [
                "activity_id" => $activity->id,
                "lat" => 52.1,
                "lng" => 21.2,
                "alt_m" => 100.5,
                "accuracy_m" => 3.4,
                "timestamp" => 1700000000,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "activity_id" => $activity->id,
                "lat" => 52.2,
                "lng" => 21.3,
                "alt_m" => null,
                "accuracy_m" => null,
                "timestamp" => 1700000001,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        $token = $user->createToken("api-token")->plainTextToken;

        $res = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->get(route("activities.export.gpx", ["id" => $activity->id]));

        $res->assertOk();
        $res->assertHeader("Content-Type", "application/gpx+xml; charset=UTF-8");
        $res->assertHeader("Content-Disposition", 'attachment; filename="activity-2026-01-01_10:00:00.gpx"');

        $xml = $res->getContent();

        $this->assertIsString($xml);
        $this->assertStringContainsString("<gpx", $xml);
        $this->assertStringContainsString("<trk", $xml);
        $this->assertStringContainsString("<trkseg", $xml);
        $this->assertStringContainsString("<trkpt", $xml);
    }

    public function testGetPhotoReturns404WhenActivityDoesNotExist(): void
    {
        $user = User::factory()->create(["email" => "u1@gmail.com"]);
        $token = $user->createToken("api-token")->plainTextToken;

        $res = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->get(route("activities.photo.show", ["id" => 9999]));

        $res->assertNotFound();
    }

    public function testGetPhotoReturns403WhenActivityIsNotOwnedByUser(): void
    {
        Storage::fake("activityPhotos");

        $user = User::factory()->create(["email" => "u1@gmail.com"]);
        $other = User::factory()->create(["email" => "u2@gmail.com"]);

        $activity = Activity::query()->create([
            "user_id" => $other->id,
            "title" => "Walk",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "walk",
            "started_at" => Carbon::parse("2026-01-02 10:00:00"),
        ]);

        Storage::disk("activityPhotos")
            ->put("activity_" . $activity->id . ".png", "PNGDATA");

        $token = $user->createToken("api-token")->plainTextToken;

        $res = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->get(route("activities.photo.show", ["id" => $activity->id]));

        $res->assertForbidden();
    }

    public function testGetPhotoReturns204WhenPhotoMissing(): void
    {
        Storage::fake("activityPhotos");

        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Mine",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00"),
        ]);

        $token = $user->createToken("api-token")->plainTextToken;

        $res = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->get(route("activities.photo.show", ["id" => $activity->id]));

        $res->assertNoContent();
    }

    public function testGetPhotoReturnsPngWithCacheHeadersWhenPhotoExists(): void
    {
        Storage::fake("activityPhotos");

        $user = User::factory()->create(["email" => "u1@gmail.com"]);

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Mine",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "run",
            "started_at" => Carbon::parse("2026-01-02 10:00:00"),
        ]);

        Storage::disk("activityPhotos")
            ->put("activity_" . $activity->id . ".png", "PNGDATA");

        $token = $user->createToken("api-token")->plainTextToken;

        $res = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->get(route("activities.photo.show", ["id" => $activity->id]));

        $res->assertOk();
        $res->assertHeader("Content-Type", "image/png");
        $res->assertHeader("Cache-Control", "max-age=31536000, public");
        $this->assertSame("PNGDATA", $res->getContent());
    }
}
