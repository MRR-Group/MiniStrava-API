<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Strava\Http\Controllers\ActivitiesController;
use Strava\Http\Controllers\Auth\LoginController;
use Strava\Http\Controllers\Auth\LogoutController;
use Strava\Http\Controllers\Auth\PasswordController;
use Strava\Http\Controllers\Auth\RegisterController;
use Strava\Http\Controllers\Profile\ProfileController;
use Strava\Http\Controllers\Profile\ProfilesController;

Route::middleware("auth:sanctum")->get("/user", fn(Request $request): JsonResponse => new JsonResponse($request->user()));

Route::middleware(["auth:sanctum"])->group(function (): void {
    Route::post("/auth/logout", [LogoutController::class, "logout"])->name("logout");

    Route::post("/activities", [ActivitiesController::class, "store"])->name("activities.store");
    Route::get("/activities", [ActivitiesController::class, "index"])->name("activities.index");
    Route::get("/activities/{id}/photo", [ActivitiesController::class, "getPhoto"])->name("activities.store");

    Route::get("/profile", [ProfileController::class, "show"])->name("profile.show");
    Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::post("/profile/avatar", [ProfileController::class, "changeAvatar"])->name("profile.avatar.update");
    Route::delete("/profile/avatar", [ProfileController::class, "deleteAvatar"])->name("profile.avatar.delete");

    Route::post("/user/change-password", [PasswordController::class, "changePassword"])->name("change-password");
});

Route::post("/auth/login", [LoginController::class, "login"])->name("login");
Route::post("/auth/register", [RegisterController::class, "register"])->name("register");
Route::post("/auth/forgot-password", [PasswordController::class, "sendResetEmail"])->name("forgot-password");
Route::post("/auth/reset-password", [PasswordController::class, "resetPassword"])->name("reset-password");

Route::get("/profiles", [ProfilesController::class, "index"])->name("profiles.index");
Route::get("/profiles/{id}", [ProfilesController::class, "show"])->name("profiles.show");
Route::get("/profiles/{id}/avatar", [ProfileController::class, "getAvatar"])->name("avatar.show");
