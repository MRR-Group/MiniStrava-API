<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\Activity\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\User;

class WeeklyUsersLeaderboard extends TableWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;
    protected static ?string $heading = "Leaderboard users";
    protected static ?int $sort = 20;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->defaultSort("distance_m", "desc")
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make("name")->label("User")->searchable()->wrap(),
                Tables\Columns\TextColumn::make("activities_count")->label("Activities")->numeric()->sortable(),
                Tables\Columns\TextColumn::make("distance_m")
                    ->label("Distance")
                    ->state(fn($record) => number_format(((int)$record->distance_m) / 1000, 2) . " km")
                    ->sortable(),
                Tables\Columns\TextColumn::make("duration_s")
                    ->label("Time")
                    ->state(fn($record) => $this->formatDuration((int)$record->duration_s))
                    ->sortable(),
            ]);
    }

    private function getQuery(): Builder
    {
        [$from, $to] = $this->resolveRange();

        $q = User::query()
            ->join("activities", "activities.user_id", "=", "users.id")
            ->whereBetween("activities.created_at", [$from, $to])
            ->groupBy("users.id", "users.name", "users.email")
            ->select([
                "users.id",
                "users.name",
                "users.email",
                DB::raw("COUNT(*) as activities_count"),
                DB::raw("SUM(activities.distance_m) as distance_m"),
                DB::raw("SUM(activities.duration_s) as duration_s"),
            ]);

        $userId = $this->userIdFilter();

        if ($userId) {
            $q->where("users.id", $userId);
        }

        return $q;
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return sprintf("%d:%02d", $h, $m);
    }
}
