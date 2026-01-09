<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Users;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Strava\Filament\Resources\Users\Pages\CreateUser;
use Strava\Filament\Resources\Users\Pages\EditUser;
use Strava\Filament\Resources\Users\Pages\ListUsers;
use Strava\Filament\Resources\Users\Pages\ViewUser;
use Strava\Filament\Resources\Users\Schemas\UserForm;
use Strava\Filament\Resources\Users\Schemas\UserInfolist;
use Strava\Filament\Resources\Users\Tables\UsersTable;
use Strava\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = "User";

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => ListUsers::route("/"),
            "create" => CreateUser::route("/create"),
            "view" => ViewUser::route("/{record}"),
            "edit" => EditUser::route("/{record}/edit"),
        ];
    }
}
