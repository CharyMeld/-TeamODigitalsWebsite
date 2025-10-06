<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')->whereNotNull('route')->update([
            'route' => DB::raw("CASE 
                WHEN route = '/dashboard' THEN 'dashboard'
                WHEN route = '/shared/profile/edit' THEN 'user-profile-information.edit'

                -- Superadmin Routes
                WHEN route = '/superadmin/users' THEN 'superadmin.users.index'
                WHEN route = '/superadmin/access-control' THEN 'superadmin.access.index'
                WHEN route = '/superadmin/finance/transactions' THEN 'superadmin.finance.transactions'
                WHEN route = '/superadmin/finance/reports' THEN 'superadmin.finance.reports'
                WHEN route = '/superadmin/menu-items' THEN 'superadmin.menu-items.index'
                WHEN route = '/superadmin/settings' THEN 'superadmin.settings.index'
                ELSE route
            END")
        ]);
    }

    public function down(): void
    {
        DB::table('menu_items')->whereNotNull('route')->update([
            'route' => DB::raw("CASE 
                WHEN route = 'dashboard' THEN '/dashboard'
                WHEN route = 'user-profile-information.edit' THEN '/shared/profile/edit'

                -- Superadmin Routes
                WHEN route = 'superadmin.users.index' THEN '/superadmin/users'
                WHEN route = 'superadmin.access.index' THEN '/superadmin/access-control'
                WHEN route = 'superadmin.finance.transactions' THEN '/superadmin/finance/transactions'
                WHEN route = 'superadmin.finance.reports' THEN '/superadmin/finance/reports'
                WHEN route = 'superadmin.menu-items.index' THEN '/superadmin/menu-items'
                WHEN route = 'superadmin.settings.index' THEN '/superadmin/settings'
                ELSE route
            END")
        ]);
    }
};

