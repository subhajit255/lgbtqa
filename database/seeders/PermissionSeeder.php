<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json  = file_get_contents(database_path() . '/data/permission.json');
        $data  = json_decode($json);
        foreach ($data->permissions as $key => $value) {
            Permission::updateOrCreate([
                'name' => $value->name,
                'slug' => Str::slug($value->name),
                'group_by' => $value->group_by
            ]);
        }
    }
}
