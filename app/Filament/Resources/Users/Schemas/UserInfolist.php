<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make("name"),
                IconEntry::make("has_premium")
                    ->label("Premium")
                    ->boolean(),
                TextEntry::make("first_name")
                    ->placeholder("-"),
                TextEntry::make("last_name")
                    ->placeholder("-"),
                TextEntry::make("email")
                    ->label("Email address"),
                TextEntry::make("email_verified_at")
                    ->dateTime()
                    ->placeholder("-"),
                TextEntry::make("birth_date")
                    ->date()
                    ->placeholder("-"),
                TextEntry::make("height")
                    ->numeric()
                    ->placeholder("-"),
                TextEntry::make("weight")
                    ->numeric()
                    ->placeholder("-"),
                TextEntry::make("gender")
                    ->badge(),
                TextEntry::make("created_at")
                    ->dateTime()
                    ->placeholder("-"),
                TextEntry::make("updated_at")
                    ->dateTime()
                    ->placeholder("-"),
            ]);
    }
}
