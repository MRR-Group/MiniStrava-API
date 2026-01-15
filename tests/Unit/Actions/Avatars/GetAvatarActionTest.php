<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Avatars;

use Illuminate\Support\Facades\Storage;
use Strava\Actions\Avatars\GetAvatarAction;
use Tests\TestCase;

class GetAvatarActionTest extends TestCase
{
    public function testReturnsAvatarContentsWhenFileExists(): void
    {
        Storage::fake("avatars");

        Storage::disk("avatars")->put("123.png", "PNGDATA");

        $action = new GetAvatarAction();

        $result = $action->execute(123);

        $this->assertSame("PNGDATA", $result);
    }

    public function testReturnsNullWhenAvatarDoesNotExist(): void
    {
        Storage::fake("avatars");

        $action = new GetAvatarAction();

        $result = $action->execute(999);

        $this->assertNull($result);
    }
}
