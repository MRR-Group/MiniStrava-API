<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Strava\Models\Activity;
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

    public function testIndexReturnsOnlyCurrentUserActivitiesSortedLatestAndPaginated(): void
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
                "created_at" => Carbon::parse("2026-01-03 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 10:00:00"),
            ],
        ]);

        $res = $this->actingAs($user)->getJson(route("activities.index"));

        $res->assertOk();
        $res->assertJsonStructure([
            "data" => [
                ["id", "title", "notes", "activity_type", "duration_s", "distance_m", "photo", "created_at"],
            ],
            "links",
            "meta",
        ]);

        $data = $res->json("data");

        $this->assertCount(2, $data);
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
        ]);

        $a2 = Activity::query()->create([
            "user_id" => $other->id,
            "title" => "NotMine",
            "notes" => null,
            "duration_s" => 100,
            "distance_m" => 1000,
            "activity_type" => "run",
        ]);

        $res = $this->actingAs($user)->getJson(route("activities.show", ["id" => $a1->id]));
        $res->assertOk();
        $res->assertJsonPath("data.id", $a1->id);

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
        $res->assertJsonValidationErrors(["title", "duration_s", "distance_m", "activity_type"]);
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
        ];

        $res = $this->actingAs($user)->postJson(route("activities.store"), $payload);

        $res->assertCreated();
        $res->assertJsonStructure([
            "data" => ["id", "title", "notes", "activity_type", "duration_s", "distance_m", "photo", "created_at"],
        ]);

        $this->assertDatabaseHas("activities", [
            "user_id" => $user->id,
            "title" => "Run",
            "duration_s" => 120,
            "distance_m" => 1500,
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
            "photo" => $file,
        ]);

        $res->assertCreated();

        $activityId = (int)$res->json("data.id");

        Storage::disk("activityPhotos")
            ->assertExists("activity_" . $activityId . ".png");
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
