<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('doctor_rating');
            $table->unsignedTinyInteger('service_rating');
            $table->unsignedTinyInteger('facility_rating');
            $table->unsignedTinyInteger('overall_rating');
            $table->text('comments')->nullable();
            $table->boolean('wa_thanks_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};