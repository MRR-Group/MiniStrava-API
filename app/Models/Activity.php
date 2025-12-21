<?php

declare(strict_types=1);

namespace Strava\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Activity
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $notes
 * @property int $duration_s
 * @property int $distance_m
 * @property string $activityType
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
        'user_id',
        'title',
        'notes',
        'duration_s',
        'distance_m',
        'activityType',
        'photo_url',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'distance_m' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function photo(): Attribute
    {
        return Attribute::get(fn(): string => url("/api/activities/{$this->id}/photo"));
    }
}
