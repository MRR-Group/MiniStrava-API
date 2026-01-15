<?php

declare(strict_types=1);

namespace Strava\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Strava\Enums\ActivityType;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $notes
 * @property int $duration_s
 * @property int $distance_m
 * @property string $activity_type
 * @property string $photo
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 */
class Activity extends Model
{
    protected $fillable = [
        "user_id",
        "title",
        "notes",
        "duration_s",
        "distance_m",
        "activity_type",
        "started_at",
    ];

    protected $casts = [
        "duration_s" => "integer",
        "distance_m" => "integer",
        "activity_type" => ActivityType::class,
    ];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<GpsPoint>
     */
    public function gpsPoints(): HasMany
    {
        return $this->hasMany(GpsPoint::class, "activity_id");
    }

    protected function photo(): Attribute
    {
        return Attribute::get(fn(): string => url("/api/activities/{$this->id}/photo"));
    }
}
