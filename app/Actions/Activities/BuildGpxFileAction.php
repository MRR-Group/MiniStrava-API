<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Carbon\Carbon;
use DateMalformedStringException;
use DateTimeImmutable;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use Strava\Models\Activity;

class BuildGpxFileAction
{
    /**
     * @throws DateMalformedStringException
     */
    public function execute(Activity $activity): GpxFile
    {
        $gpxFile = new GpxFile();

        $metadata = new Metadata();
        $metadata->name = $activity->title ?? 'Activity '.$activity->id;
        $metadata->time = new DateTimeImmutable(
            $activity->created_at->toIso8601String()
        );

        $gpxFile->metadata = $metadata;

        $track = new Track();
        $track->name = $metadata->name;

        $segment = new Segment();

        foreach ($activity->gpsPoints as $p) {
            $rawTs = $p->timestamp;

            $ts = is_string($rawTs) ? (int) $rawTs : $rawTs;

            if ($ts > 2_000_000_000) {
                $ts = intdiv($ts, 1000);
            }

            $point = new Point(Point::TRACKPOINT);
            $point->latitude = (float) $p->lat;
            $point->longitude = (float) $p->lng;
            $point->elevation = $p->alt_m !== null ? (float) $p->alt_m : null;
            $point->time = Carbon::createFromTimestampUTC($ts)->toDateTime();

            $segment->points[] = $point;
        }

        $track->segments[] = $segment;
        $gpxFile->tracks[] = $track;

        return $gpxFile;
    }
}
