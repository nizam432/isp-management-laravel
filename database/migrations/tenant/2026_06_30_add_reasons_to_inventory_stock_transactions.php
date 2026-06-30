<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `inventory_stock_transactions` MODIFY COLUMN `reason` ENUM(
            'purchase',
            'sale',
            'consumption',
            'transfer',
            'return',
            'sale_return',
            'purchase_return',
            'sale_edit',
            'sale_edit_revert',
            'sale_void',
            'sale_delete',
            'adjustment'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `inventory_stock_transactions` MODIFY COLUMN `reason` ENUM(
            'purchase',
            'sale',
            'consumption',
            'transfer',
            'return'
        ) NOT NULL");
    }
};
