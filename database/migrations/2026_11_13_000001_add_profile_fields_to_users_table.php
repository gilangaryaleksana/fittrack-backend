<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['height_cm', 'weight_kg', 'birth_date', 'gender']);
        });
    }
};
