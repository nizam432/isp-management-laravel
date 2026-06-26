<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('color', 7)->default('#185FA5');
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(0)->comment('1 = cannot be deleted');
            $table->boolean('is_active')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('income_categories')->insert([
            [
                'name'        => 'Monthly Bill',
                'slug'        => 'monthly-bill',
                'color'       => '#0F6E56',
                'icon'        => 'fas fa-file-invoice-dollar',
                'description' => 'Auto-pulled from billing payments — not for manual entry',
                'is_system'   => 1,
                'sort_order'  => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Connection Fee',
                'slug'        => 'connection-fee',
                'color'       => '#185FA5',
                'icon'        => 'fas fa-plug',
                'description' => 'New connection setup fee',
                'is_system'   => 0,
                'sort_order'  => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Cable Charge',
                'slug'        => 'cable-charge',
                'color'       => '#534AB7',
                'icon'        => 'fas fa-ethernet',
                'description' => 'Cable installation or extra cable charge',
                'is_system'   => 0,
                'sort_order'  => 3,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Equipment Sale',
                'slug'        => 'equipment-sale',
                'color'       => '#BA7517',
                'icon'        => 'fas fa-router',
                'description' => 'Router, ONU, cable, accessories sale',
                'is_system'   => 0,
                'sort_order'  => 4,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Other Income',
                'slug'        => 'other-income',
                'color'       => '#888780',
                'icon'        => 'fas fa-coins',
                'description' => 'Any other income not categorized above',
                'is_system'   => 0,
                'sort_order'  => 5,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('income_categories');
    }
};
