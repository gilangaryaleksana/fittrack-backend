<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->integer('sequence');
            $table->float('latitude');
            $table->float('longitude');
            $table->float('elevation')->nullable();
            $table->dateTime('recorded_at');
            $table->integer('heartrate')->nullable();
            $table->integer('cadence')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_streams');
    }
};
