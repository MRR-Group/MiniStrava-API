<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Carbon\Carbon;
use Strava\Actions\Activities\BuildGpxFileAction;
use Strava\Models\Activity;
use Tests\TestCase;

class BuildGpxFileActionTest extends TestCase
{
    public function testBuildsGpxWithMetadataTrackAndPointsAndConvertsTimestamps(): void
    {
        $activity = new Activity();
        $activity->id = 123;
        $activity->title = "My Run";
        $activity->started_at = Carbon::parse("2026-01-01 10:00:00", "UTC");

        $activity->setRelation("gpsPoints", collect([
            (object)[
                "lat" => "52.1",
                "lng" => "21.2",
                "alt_m" => "100.5",
                "timestamp" => "1700000000",
            ],
            (object)[
                "lat" => 53,
                "lng" => 22,
                "alt_m" => null,
                "timestamp" => 2_000_000_001_000,
            ],
        ]));

        $action = new BuildGpxFileAction();

        $gpx = $action->execute($activity);

        $this->assertSame("My Run", $gpx->metadata->name);
        $this->assertSame("My Run", $gpx->tracks[0]->name);
        $this->assertSame("2026-01-01T10:00:00+00:00", $gpx->metadata->time->format(DATE_ATOM));

        $this->assertCount(1, $gpx->tracks);
        $this->assertCount(1, $gpx->tracks[0]->segments);
        $this->assertCount(2, $gpx->tracks[0]->segments[0]->points);

        $p1 = $gpx->tracks[0]->segments[0]->points[0];
        $this->assertSame(52.1, (float)$p1->latitude);
        $this->assertSame(21.2, (float)$p1->longitude);
        $this->assertSame(100.5, (float)$p1->elevation);
        $this->assertSame(1700000000, (int)$p1->time->format("U"));

        $p2 = $gpx->tracks[0]->segments[0]->points[1];
        $this->assertSame(53.0, (float)$p2->latitude);
        $this->assertSame(22.0, (float)$p2->longitude);
        $this->assertNull($p2->elevation);
        $this->assertSame(2000000001, (int)$p2->time->format("U"));
    }

    public function testUsesFallbackNameWhenTitleIsNull(): void
    {
        $activity = new Activity();
        $activity->id = 7;
        $activity->title = null;
        $activity->started_at = Carbon::parse("2026-01-02 00:00:00", "UTC");

        $activity->setRelation("gpsPoints", collect([]));

        $action = new BuildGpxFileAction();

        $gpx = $action->execute($activity);

        $this->assertSame("Activity 7", $gpx->metadata->name);
        $this->assertSame("Activity 7", $gpx->tracks[0]->name);
    }
}
