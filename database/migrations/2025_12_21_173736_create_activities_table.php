<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("activities", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();

            $table->string("title");
            $table->text("notes")->nullable();
            $table->integer("duration_s");
            $table->integer("distance_m");
            $table->string("activity_type");
            $table->timestamp("started_at");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("activities");
    }
};
