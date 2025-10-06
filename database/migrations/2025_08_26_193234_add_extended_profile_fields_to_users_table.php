<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 10)->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'supervisor', 'employee'])
                      ->default('employee')
                      ->after('password');
            }

            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image', 255)->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 255)->default('default.png')->after('profile_image');
            }

            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id', 50)->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->nullable()->after('employee_id');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('department');
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 20)->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('marital_status');
            }

            if (!Schema::hasColumn('users', 'local_government')) {
                $table->string('local_government', 100)->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state', 100)->nullable()->after('local_government');
            }

            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after('state');
            }

            if (!Schema::hasColumn('users', 'emergency_contact')) {
                $table->string('emergency_contact', 100)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop unique index only if it exists
            if (Schema::hasColumn('users', 'username')) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('users');
                if (array_key_exists('users_username_unique', $indexes)) {
                    $table->dropUnique('users_username_unique');
                }
            }

            // Drop columns only if they exist
            $columns = [
                'username', 'gender', 'role', 'profile_image', 'avatar',
                'employee_id', 'department', 'phone', 'address',
                'marital_status', 'date_of_birth', 'local_government',
                'state', 'country', 'emergency_contact'
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

