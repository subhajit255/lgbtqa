<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleArray = [
            'Super Admin',
            'Sub Admin'
        ];
        $permissions = Permission::get();
        foreach ($roleArray as  $value) {
            Role::updateOrCreate([
                'name' => $value,
                'slug' => Str::slug($value),
            ]);
        }
        $role = Role::find(1);
        $role->permissions()->attach($permissions);
    }
}
