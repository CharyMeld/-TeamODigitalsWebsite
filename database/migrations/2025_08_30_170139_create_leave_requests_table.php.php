<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            // Employee Info
            $table->string('employee_name');
            $table->string('employee_id');
            $table->string('department');
            $table->string('job_title');
            $table->string('contact');

            // Leave Details
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('number_of_days');
            $table->text('reason');

            // Approval
            $table->string('superadmin')->nullable();
            $table->enum('status', ['Pending','Approved','Declined','Cancelled'])->default('Pending');
            $table->text('comments')->nullable();

            // Optional
            $table->string('attachment')->nullable();
            $table->boolean('employee_acknowledgement')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};

