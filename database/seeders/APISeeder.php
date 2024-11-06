<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class APISeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [
            ['id' => 4, 'name' => 'admin', 'guard_name' => 'api'],
            ['id' => 5, 'name' => 'user', 'guard_name' => 'api'],
            ['id' => 6, 'name' => 'subscriber', 'guard_name' => 'api'],

        ];

        foreach ($ads as $ad) {
            Role::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
