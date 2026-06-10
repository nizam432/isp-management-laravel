<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();               // e.g. "Salary", "ISP Bandwidth"
            $table->string('slug', 100)->unique();               // e.g. "salary", "isp-bandwidth"
            $table->string('color', 7)->default('#6c757d');      // hex color for badge UI
            $table->string('icon', 50)->nullable();              // Tabler icon name, e.g. "ti-users"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->unsignedInteger('sort_order')->default(0);   // UI display order
            $table->timestamps();
        });

        // ── Default categories ──────────────────────────────────────────
        $now = now();
        DB::table('expense_categories')->insert([
            [
                'name'        => 'Salary',
                'slug'        => 'salary',
                'color'       => '#854F0B',
                'icon'        => 'ti-users',
                'description' => 'Staff salary, bonus, overtime',
                'sort_order'  => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'ISP Bandwidth Cost',
                'slug'        => 'isp-bandwidth',
                'color'       => '#185FA5',
                'icon'        => 'ti-wifi',
                'description' => 'Upstream bandwidth, transit, peering cost',
                'sort_order'  => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Utility',
                'slug'        => 'utility',
                'color'       => '#0F6E56',
                'icon'        => 'ti-bolt',
                'description' => 'Electricity, water, gas bills',
                'sort_order'  => 3,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Office Rent',
                'slug'        => 'office-rent',
                'color'       => '#3C3489',
                'icon'        => 'ti-building',
                'description' => 'Office, warehouse, tower rent',
                'sort_order'  => 4,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Equipment & Hardware',
                'slug'        => 'equipment',
                'color'       => '#993C1D',
                'icon'        => 'ti-device-desktop',
                'description' => 'Router, switch, cable, ONU, tools',
                'sort_order'  => 5,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Vehicle & Transport',
                'slug'        => 'transport',
                'color'       => '#5F5E5A',
                'icon'        => 'ti-car',
                'description' => 'Fuel, vehicle maintenance, transport cost',
                'sort_order'  => 6,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Marketing',
                'slug'        => 'marketing',
                'color'       => '#993556',
                'icon'        => 'ti-speakerphone',
                'description' => 'Advertising, banners, social media promotion',
                'sort_order'  => 7,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Miscellaneous',
                'slug'        => 'miscellaneous',
                'color'       => '#888780',
                'icon'        => 'ti-dots',
                'description' => 'Other expenses that do not fit above',
                'sort_order'  => 8,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
