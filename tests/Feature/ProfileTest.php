<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Strava\Helpers\IdenticonHelper;
use Strava\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testShowsCurrentUserProfile(): void
    {
        $user = User::factory()->create([
            "name" => "Kacper",
            "first_name" => "Kacper",
            "last_name" => "Nazwisko",
            "email" => "kacper@example.com",
            "birth_date" => "2000-01-02",
            "height" => 180,
            "weight" => "78.50",
        ]);

        $res = $this->acting($user)->getJson("/api/profile");

        $this->assertUserResource($res, $user);
    }

    public function testUpdatesProfileAndReturnsUserResource(): void
    {
        $user = User::factory()->create([
            "name" => "Old Username",
            "first_name" => "Old",
            "last_name" => "Name",
            "email" => "old@example.com",
            "birth_date" => "1999-02-03",
            "height" => 170,
            "weight" => "70.00",
        ]);

        $payload = [
            "first_name" => "New",
            "last_name" => "Name",
            "birth_date" => "2001-10-11",
            "height" => 190,
            "weight" => "82.00",
        ];

        $res = $this->acting($user)->patchJson("/api/profile", $payload);

        $this->assertDatabaseHas("users", [
            "id" => $user->id,
            "first_name" => "New",
            "last_name" => "Name",
            "birth_date" => "2001-10-11",
            "height" => 190,
            "weight" => "82.00",
        ]);

        $this->assertUserResource($res, $user);
    }

    public function testChangesAvatarAndStoresPngOnAvatarsDisk(): void
    {
        Storage::fake("avatars");

        $user = User::factory()->create([
            "birth_date" => "2000-01-02",
            "height" => 180,
            "weight" => "78.50",
        ]);

        $file = UploadedFile::fake()->create("avatar.png", 10, "image/png");

        $res = $this->acting($user)->postJson("/api/profile/avatar", [
            "avatar" => $file,
        ]);

        $this->assertUserResource($res, $user);
        Storage::disk("avatars")->assertExists($user->id . ".png");
    }

    public function testReturnsPngAvatarIfExistsWithLongCacheHeaders(): void
    {
        Storage::fake("avatars");

        $user = User::factory()->create();
        Storage::disk("avatars")->put($user->id . ".png", "PNGDATA");

        $res = $this->get("/api/profiles/" . $user->id . "/avatar");

        $res->assertOk();
        $res->assertHeader("Content-Type", "image/png");
        $res->assertHeader("Cache-Control", "max-age=31536000, public");
        $this->assertSame("PNGDATA", $res->getContent());
    }

    public function testReturnsSvgIdenticonIfAvatarMissingWithShorterCacheHeaders(): void
    {
        Storage::fake("avatars");

        $user = User::factory()->create();

        $res = $this->get("/api/profiles/" . $user->id . "/avatar");

        $res->assertOk();
        $res->assertHeader("Content-Type", "image/svg+xml");
        $res->assertHeader("Cache-Control", "max-age=86400, public");
        $this->assertStringContainsString("<svg", $res->getContent());
    }

    public function testDeletesAvatarIfExists(): void
    {
        Storage::fake("avatars");

        $user = User::factory()->create([
            "birth_date" => "2000-01-02",
            "height" => 180,
            "weight" => "78.50",
        ]);

        Storage::disk("avatars")->put($user->id . ".png", "PNGDATA");

        $res = $this->acting($user)->deleteJson("/api/profile/avatar");

        $this->assertUserResource($res, $user);
        Storage::disk("avatars")->assertMissing($user->id . ".png");
    }

    public function testReturnsOkEvenIfAvatarDoesNotExistOnDelete(): void
    {
        Storage::fake("avatars");

        $user = User::factory()->create([
            "birth_date" => "2000-01-02",
            "height" => 180,
            "weight" => "78.50",
        ]);

        $res = $this->acting($user)->deleteJson("/api/profile/avatar");

        $this->assertUserResource($res, $user);
        Storage::disk("avatars")->assertMissing($user->id . ".png");
    }

    private function acting(User $user): self
    {
        return $this->actingAs($user);
    }

    private function assertUserResource($res, User $user): void
    {
        $user->refresh();

        $res->assertOk();
        $res->assertJsonStructure([
            "data" => [
                "id",
                "email",
                "name",
                "first_name",
                "last_name",
                "birth_date",
                "height",
                "weight",
                "avatar",
                "created_at",
            ],
        ]);

        $data = $res->json("data");

        $this->assertSame($user->id, $data["id"]);
        $this->assertSame($user->email, $data["email"]);
        $this->assertSame($user->name, $data["name"]);
        $this->assertSame($user->first_name, $data["first_name"]);
        $this->assertSame($user->last_name, $data["last_name"]);
        $this->assertSame($user->height, $data["height"]);
        $this->assertSame($user->weight, $data["weight"]);

        $this->assertSame(IdenticonHelper::url($user->id), $data["avatar"]);

        $expectedBirth = (string)$user->birth_date;
        $this->assertSame($expectedBirth, (string)$data["birth_date"]);

        $this->assertSame($user->created_at->toJSON(), $data["created_at"]);
    }
}
