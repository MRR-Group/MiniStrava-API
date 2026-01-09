<?php

namespace Strava\Filament\Resources\Activities\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Strava\Filament\Resources\Activities\ActivityResource;
use Strava\Filament\Resources\Activities\Widgets\ActivityStats;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityStats::class,
        ];
    }
}
