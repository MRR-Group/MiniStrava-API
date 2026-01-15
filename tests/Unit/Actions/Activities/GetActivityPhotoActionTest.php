<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Illuminate\Support\Facades\Storage;
use Strava\Actions\Activities\GetActivityPhotoAction;
use Tests\TestCase;

class GetActivityPhotoActionTest extends TestCase
{
    public function testReturnsPhotoContentsWhenFileExists(): void
    {
        Storage::fake("activityPhotos");

        Storage::disk("activityPhotos")
            ->put("activity_123.png", "PNGDATA");

        $action = new GetActivityPhotoAction();

        $result = $action->execute(123);

        $this->assertSame("PNGDATA", $result);
    }

    public function testReturnsNullWhenPhotoDoesNotExist(): void
    {
        Storage::fake("activityPhotos");

        $action = new GetActivityPhotoAction();

        $result = $action->execute(999);

        $this->assertNull($result);
    }
}
