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
        Schema::table('users', function (Blueprint $table) {
            // Add only the missing fields
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['developer', 'superadmin', 'admin', 'employee'])->default('employee')->after('password');
            }

            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->unique()->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('employee_id');
            }

            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('status');
            }

            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }

            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('marital_status');
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'local_government')) {
                $table->string('local_government')->nullable()->after('emergency_contact');
            }

            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('local_government');
            }

            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->default('Nigeria')->after('state');
            }

            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('country');
            }

            if (!Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 12, 2)->nullable()->after('department');
            }

            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('salary');
            }

            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path', 2048)->nullable()->after('hire_date');
            }

            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('profile_photo_path');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('users', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);

            // Then drop columns
            $table->dropColumn([
                'username',
                'role',
                'employee_id',
                'status',
                'gender',
                'date_of_birth',
                'marital_status',
                'phone',
                'address',
                'emergency_contact',
                'local_government',
                'state',
                'country',
                'department',
                'salary',
                'hire_date',
                'profile_photo_path',
                'created_by',
                'updated_by',
                'deleted_by',
            ]);
        });
    }

};

