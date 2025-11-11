<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AccessRights;

class AccessRightRoleSeeder extends Seeder
{
    public function run()
    {
        // Get all access rights
        $accessRights = AccessRights::all();

        // Admin role_id = 1
        foreach ($accessRights as $right) {
            DB::table('access_right_role')->updateOrInsert([
                'role_id' => 1,
                'access_right_id' => $right->id
            ]);
        }

        // Salesperson role_id = 2, exclude DELETE routes
        foreach ($accessRights as $right) {
            if (str_contains($right->route_name, '.destroy')) {
                continue; // skip delete routes
            }

            DB::table('access_right_role')->updateOrInsert([
                'role_id' => 2,
                'access_right_id' => $right->id
            ]);
        }
    }
}
