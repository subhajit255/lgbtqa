<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Request $request): void
    {
        $faker = Faker::create();

        // Super Admin User
        $permissions = Permission::all();
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $superAdminUser = User::where('email', 'souravsamantapappu@gmail.com')->first();

        if (empty($superAdminUser)) {
            $superAdminUser = new User;
            $superAdminUser->uuid = $faker->uuid;
            $superAdminUser->name = 'Super Admin';
            $superAdminUser->username = 'SuperAdmin';
            $superAdminUser->user_type = 1;
            $superAdminUser->email = 'souravsamantapappu@gmail.com';
            $superAdminUser->phone_code = 91;
            $superAdminUser->mobile_number = 8906226970;
            $superAdminUser->password = bcrypt('admin@123');
            $superAdminUser->original_password = 'admin@123';
            $superAdminUser->country_id = 101;
            $superAdminUser->state_id = 41;
            $superAdminUser->city_id = 5583;
            $superAdminUser->is_active = 1;
            $superAdminUser->is_verified_email = 1;
            $superAdminUser->is_verified_phone = 1;
            $superAdminUser->is_approve = 1;
            $superAdminUser->is_blocked = 0;
            $superAdminUser->last_login_ip = $request->getClientIp();
            $superAdminUser->last_login_at = date('Y-m-d H:i:s');
            $superAdminUser->save();
        }

        $superAdminUser->roles()->sync($superAdminRole);
        $superAdminUser->permissions()->sync($permissions);
        $superAdminRole->permissions()->sync($permissions);
    }
}
