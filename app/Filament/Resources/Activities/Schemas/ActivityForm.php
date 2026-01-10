<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make("user_id")
                    ->relationship("user", "name")
                    ->required(),
                TextInput::make("title")
                    ->required(),
                Textarea::make("notes")
                    ->required()
                    ->columnSpanFull(),
                TextInput::make("duration_s")
                    ->required()
                    ->numeric(),
                TextInput::make("distance_m")
                    ->required()
                    ->numeric(),
                TextInput::make("activityType")
                    ->required(),
            ]);
    }
}
