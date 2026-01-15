<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Avatars;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Strava\Actions\Avatars\ChangeAvatarAction;
use Tests\TestCase;

class ChangeAvatarActionTest extends TestCase
{
    public function testStoresAvatarPngOnAvatarsDiskAndReturnsTrue(): void
    {
        Storage::fake("avatars");

        $file = UploadedFile::fake()->create("avatar.png", 10, "image/png");

        $action = new ChangeAvatarAction();

        $result = $action->execute($file, 123);

        $this->assertTrue($result);

        Storage::disk("avatars")->assertExists("123.png");
    }
}
