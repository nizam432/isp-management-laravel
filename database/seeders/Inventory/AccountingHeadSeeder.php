<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;

class AccountingHeadSeeder extends Seeder
{
    public function run(): void
    {
        // ── Income Categories (Fixed / System) ────────────────────

        $incomeHeads = [
            [
                'name'      => 'Product Sale',
                'slug'      => 'product_sale',
                'is_system' => true,
            ],
            [
                'name'      => 'Sale Return',
                'slug'      => 'sale_return',
                'is_system' => true,
            ],
        ];

        foreach ($incomeHeads as $head) {
            IncomeCategory::updateOrCreate(
                ['slug' => $head['slug']],
                $head
            );
        }

        // ── Expense Categories (Fixed / System) ───────────────────

        $expenseHeads = [
            [
                'name'      => 'Stock Purchase',
                'slug'      => 'stock_purchase',
                'is_system' => true,
            ],
            [
                'name'      => 'Consumption Expense',
                'slug'      => 'consumption_expense',
                'is_system' => true,
            ],
            [
                'name'      => 'Inventory Loss',
                'slug'      => 'inventory_loss',
                'is_system' => true,
            ],
            [
                'name'      => 'Purchase Return',
                'slug'      => 'purchase_return',
                'is_system' => true,
            ],
        ];

        foreach ($expenseHeads as $head) {
            ExpenseCategory::updateOrCreate(
                ['slug' => $head['slug']],
                $head
            );
        }

        $this->command->info('✅ Inventory Accounting Heads seeded successfully.');
    }
}
