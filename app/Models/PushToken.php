<?php

declare(strict_types=1);

namespace Strava\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $user_id
 * @property string token
 * @property string platform
 * @property string device_id
 * @property string device_name
 * @property Carbon last_used_at
 * @property-read User $user
 */
class PushToken extends Model
{
    protected $fillable = [
        "user_id",
        "token",
        "platform",
        "device_id",
        "device_name",
        "last_used_at",
    ];
    protected $casts = [
        "last_used_at" => "datetime",
    ];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
