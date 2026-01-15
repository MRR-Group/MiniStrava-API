<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Strava\Enums\Gender;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("name")
                    ->required(),
                TextInput::make("first_name"),
                TextInput::make("last_name"),
                TextInput::make("email")
                    ->label("Email address")
                    ->email()
                    ->required(),
                DateTimePicker::make("email_verified_at"),
                TextInput::make("password")
                    ->password()
                    ->required(),
                DatePicker::make("birth_date"),
                TextInput::make("height")
                    ->numeric(),
                TextInput::make("weight")
                    ->numeric(),
                Select::make("gender")
                    ->options(Gender::class)
                    ->default("male")
                    ->required(),
                Toggle::make("has_premium")
                    ->label("Premium")
                    ->default(false),
            ]);
    }
}
