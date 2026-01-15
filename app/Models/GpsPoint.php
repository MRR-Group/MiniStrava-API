<?php

declare(strict_types=1);

namespace Strava\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $activity_id
 * @property int    $seq
 * @property float  $lat
 * @property float  $lng
 * @property float  $alt_m
 * @property float  $accuracy_m
 * @property int    $timestamp
 * @property-read Activity $activity
 */
class GpsPoint extends Model
{
    protected $fillable = [
        "activity_id",
        "lat",
        "lng",
        "alt_m",
        "accuracy_m",
        "timestamp",
    ];
    protected $casts = [
        "lat" => "float",
        "lng" => "float",
        "alt_m" => "float",
        "accuracy_m" => "float",
        "timestamp" => "integer",
    ];

    /**
     * @return BelongsTo<Activity>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, "activity_id");
    }
}
