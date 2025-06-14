<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class CreateTestAdmin extends Command
{
    protected $signature = 'admin:create-test';
    protected $description = 'Create a test admin user';

    public function handle()
    {
        // Create admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Create test admin user
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@nsgmoa.test',
            'password' => bcrypt('Test123!')
        ]);

        // Attach admin role
        $user->roles()->attach($role->id);

        $this->info('Test admin user created successfully!');
    }
}
