<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Run: php artisan db:seed --class=SuperAdminUserSeeder --force
     * Safe to run multiple times (updateOrCreate).
     */
    public function run(): void
    {
        $admin = SuperAdmin::updateOrCreate(
            ['email' => 'admin@isp.com'],
            [
                'name'      => 'Super Admin',
                'password'  => 'ChangeThisPassword123!', // auto-hashed via model cast
                'is_active' => true,
            ]
        );

        $this->command->info("Super Admin created/updated: {$admin->email}");
        $this->command->warn('⚠️  লগইন করার সাথে সাথেই এই default password পরিবর্তন করুন।');
    }
}
