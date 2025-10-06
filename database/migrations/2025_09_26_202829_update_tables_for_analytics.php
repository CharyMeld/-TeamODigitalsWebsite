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
        // Update attendances table if needed
        if (!Schema::hasColumn('attendances', 'status')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('status', ['present', 'absent', 'break', 'late'])->default('present')->after('user_id');
            });
        }

        if (!Schema::hasColumn('attendances', 'check_in_time')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->time('check_in_time')->nullable()->after('date');
                $table->time('check_out_time')->nullable()->after('check_in_time');
            });
        }

        // Update assign_tasks table if needed
        if (!Schema::hasColumn('assign_tasks', 'status')) {
            Schema::table('assign_tasks', function (Blueprint $table) {
                $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending')->after('description');
            });
        }

        if (!Schema::hasColumn('assign_tasks', 'due_date')) {
            Schema::table('assign_tasks', function (Blueprint $table) {
                $table->date('due_date')->nullable()->after('status');
            });
        }

        // Update leave_requests table if needed
        if (!Schema::hasColumn('leave_requests', 'status')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('reason');
            });
        }

        // Update users table if needed (for employee role tracking)
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'manager', 'employee'])->default('employee')->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns (be careful with this in production)
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['status', 'check_in_time', 'check_out_time']);
        });

        Schema::table('assign_tasks', function (Blueprint $table) {
            $table->dropColumn(['status', 'due_date']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status']);
        });
    }
};
