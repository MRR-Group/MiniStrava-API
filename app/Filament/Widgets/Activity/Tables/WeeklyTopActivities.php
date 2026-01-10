<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\Activity\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class WeeklyTopActivities extends TableWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;
    protected static ?string $heading = "Top activities";
    protected static ?int $sort = 21;

    public function table(Table $table): Table
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween("created_at", [$from, $to]);

        $q = $this->applyUserFilter($q);

        return $table
            ->query($q)
            ->defaultSort("distance_m", "desc")
            ->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make("id")
                    ->label("ID")
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make("activityType")
                    ->label("Type")
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ?: "unknown"),

                Tables\Columns\TextColumn::make("distance_m")
                    ->label("Distance")
                    ->state(fn($record) => number_format(((int)$record->distance_m) / 1000, 2) . " km")
                    ->sortable(),

                Tables\Columns\TextColumn::make("duration_s")
                    ->label("Time")
                    ->state(fn($record) => $this->formatDuration((int)$record->duration_s))
                    ->sortable(),

                Tables\Columns\TextColumn::make("created_at")
                    ->label("Date")
                    ->dateTime("d.m.Y H:i")
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label("Open")
                    ->url(fn($record) => route("filament.admin.resources.activities.view", $record))
                    ->openUrlInNewTab(),
            ]);
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return sprintf("%d:%02d", $h, $m);
    }
}
