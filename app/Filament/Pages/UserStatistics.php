<?php

declare(strict_types=1);

namespace Strava\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Strava\Filament\Widgets\User\Charts\ActiveUsersPerDayChart;
use Strava\Filament\Widgets\User\Stats\UsersOverview;
use UnitEnum;

class UserStatistics extends Dashboard
{
    use HasFiltersForm;

    protected static string|null|BackedEnum $navigationIcon = "heroicon-o-chart-bar";
    protected static ?string $navigationLabel = "User statistics";
    protected static string|null|UnitEnum $navigationGroup = null;
    protected static ?int $navigationSort = 30;
    protected static string $routePath = "user-stats";
    protected static bool $filtersFormInHeader = true;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make("Filters")
                ->columnSpanFull()
                ->schema([
                    Grid::make(6)->schema([
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
                ]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            UsersOverview::class,
            ActiveUsersPerDayChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
