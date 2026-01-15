<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Strava\Actions\Activities\StoreActivityPhotoAction;
use Tests\TestCase;

class StoreActivityPhotoActionTest extends TestCase
{
    public function testStoresPhotoAsActivityPngOnActivityPhotosDisk(): void
    {
        Storage::fake("activityPhotos");

        $file = UploadedFile::fake()->create("photo.png", 10, "image/png");

        $action = new StoreActivityPhotoAction();

        $result = $action->execute($file, 123);

        $this->assertSame("activity_123.png", $result);

        Storage::disk("activityPhotos")->assertExists("activity_123.png");
    }
}
