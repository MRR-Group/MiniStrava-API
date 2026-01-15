<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Avatars;

use Illuminate\Support\Facades\Storage;
use Strava\Actions\Avatars\DeleteAvatarAction;
use Tests\TestCase;

class DeleteAvatarActionTest extends TestCase
{
    public function testDeletesExistingAvatarAndReturnsTrue(): void
    {
        Storage::fake("avatars");

        Storage::disk("avatars")->put("123.png", "PNGDATA");

        $action = new DeleteAvatarAction();

        $result = $action->execute(123);

        $this->assertTrue($result);
        Storage::disk("avatars")->assertMissing("123.png");
    }

    public function testReturnsFalseWhenAvatarDoesNotExist(): void
    {
        Storage::fake("avatars");

        $action = new DeleteAvatarAction();

        $result = $action->execute(999);

        $this->assertFalse($result);
    }
}
