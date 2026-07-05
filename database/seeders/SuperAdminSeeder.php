<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates the very first Super Admin user for a fresh central database
     * deployment. Safe to run multiple times — uses updateOrCreate so it won't
     * create duplicates if run again.
     *
     * Run: php artisan db:seed --class=SuperAdminSeeder --force
     */
    public function run(): void 
    {
        // Ensure the 'Super Admin' role exists (spatie/laravel-permission).
        // Uses the default 'web' guard — adjust if your app uses a different one.
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        $user = User::updateOrCreate(
            ['email' => 'admin@isp.com'], 
            [
                'name'      => 'Super Admin',
                'password'  => 'ChangeThisPassword123!', // hashed automatically via the 'hashed' cast on User model
                'is_active' => true,
            ]
        );

        if (!$user->hasRole('Super Admin')) {
            $user->assignRole($role);
        }

        $this->command->info("Super Admin created/updated: {$user->email}");
        $this->command->warn('⚠️  Login করার সাথে সাথেই এই default password টা পরিবর্তন করে নিন (Profile পেজ থেকে)।');
    }
}
