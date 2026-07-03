<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('max_slots')->default(20);
            $table->boolean('is_available')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};