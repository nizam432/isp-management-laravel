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

    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
