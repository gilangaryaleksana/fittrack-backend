<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->float('distance')->default(0);
            $table->integer('moving_time')->default(0);
            $table->integer('elapsed_time')->default(0);
            $table->float('elevation_gain')->default(0);
            $table->dateTime('start_date');
            $table->float('start_lat')->nullable();
            $table->float('start_lng')->nullable();
            $table->float('end_lat')->nullable();
            $table->float('end_lng')->nullable();
            $table->float('average_speed')->nullable();
            $table->float('max_speed')->nullable();
            $table->float('calories')->default(0);
            $table->enum('visibility', ['public', 'followers', 'private'])->default('public');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
