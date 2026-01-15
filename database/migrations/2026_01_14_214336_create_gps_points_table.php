<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("gps_points", function (Blueprint $table): void {
            $table->integer("id");
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();

            $table->double("lat");
            $table->double("lng");

            $table->double("alt_m")->nullable();
            $table->double("accuracy_m")->nullable();

            $table->integer("timestamp");


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("gps_points");
    }
};
