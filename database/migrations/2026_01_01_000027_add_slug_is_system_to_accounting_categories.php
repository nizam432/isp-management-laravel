<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // income_categories table
        Schema::table('income_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('income_categories', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('income_categories', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('slug')
                      ->comment('true হলে delete/edit করা যাবে না');
            }
        });

        // expense_categories table
        Schema::table('expense_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_categories', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('expense_categories', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('slug')
                      ->comment('true হলে delete/edit করা যাবে না');
            }
        });
    }

    public function down(): void
    {
        Schema::table('income_categories', function (Blueprint $table) {
            if (Schema::hasColumn('income_categories', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('income_categories', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });

        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('expense_categories', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });
    }
};
