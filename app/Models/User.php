<?php

declare(strict_types=1);

namespace Strava\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Strava\Enums\Gender;
use Strava\Helpers\IdenticonHelper;

/**
 * @property int $id
 * @property string $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string $password
 * @property Carbon $birth_date
 * @property int|null $height
 * @property string|null $weight
 * @property Gender $gender
 * @property string $avatar
 * @property Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        "name",
        "email",
        "password",
        "first_name",
        "last_name",
        "birth_date",
        "height",
        "weight",
        "gender",
    ];
    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "birth_date" => "date",
            "password" => "hashed",
            "gender" => Gender::class,
        ];
    }

    protected function avatar(): Attribute
    {
        return Attribute::get(fn(): string => IdenticonHelper::url($this->id));
    }
}
