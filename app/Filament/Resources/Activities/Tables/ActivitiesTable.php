<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Activities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("user.name")
                    ->searchable(),
                TextColumn::make("title")
                    ->searchable(),
                TextColumn::make("duration_s")
                    ->numeric()
                    ->sortable(),
                TextColumn::make("distance_m")
                    ->numeric()
                    ->sortable(),
                TextColumn::make("activity_type")
                    ->searchable(),
                TextColumn::make("started_at")
                    ->dateTime()
                    ->sortable(),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
