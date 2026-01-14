<?php

declare(strict_types=1);

namespace Strava\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsPoint extends Model
{
    protected $fillable = [
        "activity_id",
        "seq",
        "lat",
        "lng",
        "alt_m",
        "accuracy_m",
        "timestamp",
    ];
    protected $casts = [
        "seq" => "integer",
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
