<?php

namespace Strava\Filament\Resources\Activities\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Strava\Filament\Resources\Activities\ActivityResource;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
