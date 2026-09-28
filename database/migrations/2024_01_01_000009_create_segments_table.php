<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('activity_type_id')->constrained();
            $table->float('distance');
            $table->float('average_grade')->nullable();
            $table->float('elevation_gain')->nullable();
            $table->float('start_lat');
            $table->float('start_lng');
            $table->float('end_lat');
            $table->float('end_lng');
            $table->string('city', 100)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
