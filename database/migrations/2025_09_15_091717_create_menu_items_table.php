<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('menu_items')->where('route', '/dashboard')
            ->update(['route' => 'dashboard']);

        DB::table('menu_items')->where('route', '/shared/profile/edit')
            ->update(['route' => 'user-profile-information.update']);

        // Superadmin Routes
        DB::table('menu_items')->where('route', '/superadmin/users')
            ->update(['route' => 'superadmin.users.index']);

        DB::table('menu_items')->where('route', '/superadmin/access-control')
            ->update(['route' => 'superadmin.access.index']);

        DB::table('menu_items')->where('route', '/superadmin/finance/transactions')
            ->update(['route' => 'superadmin.finance.transactions']);

        DB::table('menu_items')->where('route', '/superadmin/finance/reports')
            ->update(['route' => 'superadmin.finance.reports']);

        DB::table('menu_items')->where('route', '/superadmin/menu-items')
            ->update(['route' => 'superadmin.menu-items.index']);

        DB::table('menu_items')->where('route', '/superadmin/settings')
            ->update(['route' => 'superadmin.settings.index']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert routes back to old values
        DB::table('menu_items')->where('route', 'dashboard')
            ->update(['route' => '/dashboard']);

        DB::table('menu_items')->where('route', 'user-profile-information.update')
            ->update(['route' => '/shared/profile/edit']);

        // Superadmin Routes
        DB::table('menu_items')->where('route', 'superadmin.users.index')
            ->update(['route' => '/superadmin/users']);

        DB::table('menu_items')->where('route', 'superadmin.access.index')
            ->update(['route' => '/superadmin/access-control']);

        DB::table('menu_items')->where('route', 'superadmin.finance.transactions')
            ->update(['route' => '/superadmin/finance/transactions']);

        DB::table('menu_items')->where('route', 'superadmin.finance.reports')
            ->update(['route' => '/superadmin/finance/reports']);

        DB::table('menu_items')->where('route', 'superadmin.menu-items.index')
            ->update(['route' => '/superadmin/menu-items']);

        DB::table('menu_items')->where('route', 'superadmin.settings.index')
            ->update(['route' => '/superadmin/settings']);
    }
};

