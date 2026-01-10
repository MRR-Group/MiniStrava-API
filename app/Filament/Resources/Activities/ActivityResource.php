<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Activities;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Strava\Filament\Resources\Activities\Pages\CreateActivity;
use Strava\Filament\Resources\Activities\Pages\EditActivity;
use Strava\Filament\Resources\Activities\Pages\ListActivities;
use Strava\Filament\Resources\Activities\Schemas\ActivityForm;
use Strava\Filament\Resources\Activities\Tables\ActivitiesTable;
use Strava\Filament\Resources\Activities\Widgets\ActivityStats;
use Strava\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = "Activity";

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => ListActivities::route("/"),
            "create" => CreateActivity::route("/create"),
            "edit" => EditActivity::route("/{record}/edit"),
            "view" => Pages\ViewActivity::route("/{record}"),
        ];
    }
}
