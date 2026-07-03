<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('poly_id')->constrained('polies')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->unsignedSmallInteger('queue_number');
            $table->date('queue_date');
            $table->time('estimated_time')->nullable();
            $table->enum('status', ['waiting', 'called', 'in_progress', 'done', 'late', 'cancelled', 'rescheduled'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->boolean('late_handled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};