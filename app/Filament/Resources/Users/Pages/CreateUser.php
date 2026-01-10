<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use Strava\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
