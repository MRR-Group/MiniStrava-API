<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use Illuminate\Http\Request;
use Strava\Http\Resources\GpsPointResource;
use Strava\Models\GpsPoint;
use Tests\TestCase;

class GpsPointResourceTest extends TestCase
{
    public function testTransformsGpsPointToExpectedArray(): void
    {
        $gps = new GpsPoint([
            "lat" => 52.1,
            "lng" => 21.2,
            "alt_m" => 100.5,
            "accuracy_m" => 3.4,
            "timestamp" => 1700000000,
        ]);

        $resource = GpsPointResource::make($gps);

        $data = $resource->toArray(Request::create("/", "GET"));

        $this->assertSame([
            "lat" => 52.1,
            "lng" => 21.2,
            "alt_m" => 100.5,
            "accuracy_m" => 3.4,
            "timestamp" => 1700000000,
        ], $data);
    }

    public function testAllowsNullableFields(): void
    {
        $gps = new GpsPoint([
            "lat" => 52.1,
            "lng" => 21.2,
            "alt_m" => null,
            "accuracy_m" => null,
            "timestamp" => 1700000000,
        ]);

        $resource = GpsPointResource::make($gps);

        $data = $resource->toArray(Request::create("/", "GET"));

        $this->assertSame([
            "lat" => 52.1,
            "lng" => 21.2,
            "alt_m" => null,
            "accuracy_m" => null,
            "timestamp" => 1700000000,
        ], $data);
    }
}
