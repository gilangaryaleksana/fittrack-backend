<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('segment_efforts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('elapsed_time');
            $table->integer('moving_time');
            $table->dateTime('start_date');
            $table->integer('kom_rank')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segment_efforts');
    }
};
