<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'half_day'])->default('absent');
            $table->enum('break_status', ['none', 'on_break', 'ended'])->default('none');
            $table->time('current_break_start')->nullable();
            $table->integer('total_break_time')->default(0); // minutes
            $table->integer('working_hours')->default(0);     // minutes
            $table->json('working_sessions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

