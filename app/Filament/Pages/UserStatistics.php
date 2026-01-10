<?php

namespace Strava\Filament\Pages;

use Filament\Pages\Page;

class UserStatistics extends Page
{
    protected string $view = 'filament.pages.user-statistics';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'User statistics';
    protected static string|null|\UnitEnum $navigationGroup = null;
    protected static ?int $navigationSort = 30;

    protected static string $routePath = 'user-stats';

    public function getWidgets(): array
    {
        return [
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
