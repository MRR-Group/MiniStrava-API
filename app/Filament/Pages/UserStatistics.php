<?php

declare(strict_types=1);

namespace Strava\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class UserStatistics extends Page
{
    protected static string|null|BackedEnum $navigationIcon = "heroicon-o-chart-bar";
    protected static ?string $navigationLabel = "User statistics";
    protected static string|null|UnitEnum $navigationGroup = null;
    protected static ?int $navigationSort = 30;
    protected static string $routePath = "user-stats";
    protected string $view = "filament.pages.user-statistics";

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
