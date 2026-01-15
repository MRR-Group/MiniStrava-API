<?php

declare(strict_types=1);

namespace Strava\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Strava\Http\Requests\PushTokenStoreRequest;
use Strava\Models\PushToken;

class PushTokenController extends Controller
{
    public function store(PushTokenStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $token = PushToken::query()->updateOrCreate(
            ["token" => $data["token"]],
            [
                "user_id" => $user->id,
                "platform" => $data["platform"],
                "device_id" => $data["device_id"] ?? null,
                "device_name" => $data["device_name"] ?? null,
                "last_used_at" => Carbon::now(),
            ],
        );

        return response()->json([
            "ok" => true,
            "id" => $token->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            "token" => ["required", "string", "max:4096"],
        ]);

        $user = $request->user();

        PushToken::query()
            ->where("user_id", $user->id)
            ->where("token", $request->string("token")->toString())
            ->delete();

        return response()->json(["ok" => true]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $tokens = PushToken::query()
            ->where("user_id", $user->id)
            ->orderByDesc("last_used_at")
            ->get(["id", "platform", "device_id", "device_name", "last_used_at", "created_at"]);

        return response()->json([
            "ok" => true,
            "data" => $tokens,
        ]);
    }
}
