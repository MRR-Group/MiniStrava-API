<?php

declare(strict_types=1);

namespace Strava\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Strava\Enums\ActivityType;
use Strava\Filament\Widgets\Activity\Charts\ActivitiesPerDayChart;
use Strava\Filament\Widgets\Activity\Charts\ActivityTypeShareChart;
use Strava\Filament\Widgets\Activity\Charts\DistancePerDayChart;
use Strava\Filament\Widgets\Activity\Charts\DurationPerDayChart;
use Strava\Filament\Widgets\Activity\Stats\ActivityAveragesOverview;
use Strava\Filament\Widgets\Activity\Stats\ActivityRecordsOverview;
use Strava\Filament\Widgets\Activity\Stats\ActivityStreakInsight;
use Strava\Filament\Widgets\Activity\Stats\MostActiveHourInsight;
use Strava\Filament\Widgets\Activity\Stats\WeekVsLastWeekInsight;
use Strava\Filament\Widgets\Activity\Tables\WeeklyTopActivities;
use Strava\Filament\Widgets\Activity\Tables\WeeklyUsersLeaderboard;
use Strava\Models\User;
use UnitEnum;

class ActivityStats extends Dashboard
{
    use HasFiltersForm;

    protected static string|null|BackedEnum $navigationIcon = "heroicon-o-chart-bar";
    protected static ?string $navigationLabel = "Activity statistics";
    protected static string|null|UnitEnum $navigationGroup = null;
    protected static ?int $navigationSort = 30;
    protected static string $routePath = "activity-stats";
    protected string|Width|null $maxContentWidth = "full";

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make("Filters")
                ->columnSpanFull()
                ->schema([
                    Grid::make(6)->schema([
                        Select::make("user_id")
                            ->label("User")
                            ->options(fn() => User::query()->orderBy("name")->pluck("name", "id"))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(3),

                        Select::make("range")
                            ->label("Range")
                            ->options([
                                "7" => "Last 7 days",
                                "30" => "Last 30 days",
                                "90" => "Last 90 days",
                                "this_week" => "This week",
                                "last_week" => "Last week",
                                "custom" => "Custom",
                            ])
                            ->default("30")
                            ->live()
                            ->columnSpan(3),
                    ]),

                    Grid::make(6)->schema([
                        DatePicker::make("from")
                            ->label("From")
                            ->visible(fn($get) => $get("range") === "custom")
                            ->live()
                            ->columnSpan(3),

                        DatePicker::make("to")
                            ->label("To")
                            ->visible(fn($get) => $get("range") === "custom")
                            ->live()
                            ->columnSpan(3),
                    ]),

                    Grid::make(6)->schema([
                        TextInput::make("distance_min_km")
                            ->label("Min distance (km)")
                            ->numeric()
                            ->columnSpan(2),

                        TextInput::make("distance_max_km")
                            ->label("Max distance (km)")
                            ->numeric()
                            ->columnSpan(2),

                        Select::make("activity_type")
                            ->label("Activity type")
                            ->options(ActivityType::options())
                            ->searchable()
                            ->placeholder("Any")
                            ->columnSpan(2),
                    ]),
                ]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            WeekVsLastWeekInsight::class,
            ActivityRecordsOverview::class,
            ActivityAveragesOverview::class,
            MostActiveHourInsight::class,
            ActivityStreakInsight::class,
            ActivitiesPerDayChart::class,
            ActivityTypeShareChart::class,
            DistancePerDayChart::class,
            DurationPerDayChart::class,
            WeeklyTopActivities::class,
            WeeklyUsersLeaderboard::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
