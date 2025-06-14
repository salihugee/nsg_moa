<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Create admin role
        $adminRole = Role::create([
            'name' => 'admin'
        ]);

        // Create test admin user
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@nsgmoa.test',
            'password' => bcrypt('Test123!')
        ]);

        // Assign admin role
        $user->roles()->attach($adminRole->id);
    }
}
