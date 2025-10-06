<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assign_tasks', function (Blueprint $table) {
            $table->id(); // 'id' column
            $table->string('admin_id', 50)->nullable();
            $table->string('employee_id', 50)->nullable();
            $table->string('task_title', 255)->nullable();
            $table->string('task_type', 100)->nullable();
            $table->string('task_priority', 50)->nullable();
            $table->date('deadline')->nullable();
            $table->string('status', 50)->default('Pending');
            $table->timestamps(); // creates 'created_at' and 'updated_at'
            $table->string('work_location', 50)->nullable();
            $table->string('project_name', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->text('task_description')->nullable();
            $table->text('files_worked_on')->nullable();
            $table->text('file_names_or_ids')->nullable();
            $table->string('goals_met', 50)->nullable();
            $table->text('collaborators')->nullable();
            $table->text('issues_encountered')->nullable();
            $table->text('unfinished_task')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->string('reviewed_by_admin_id', 50)->nullable();
            $table->dateTime('reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_tasks');
    }
};

