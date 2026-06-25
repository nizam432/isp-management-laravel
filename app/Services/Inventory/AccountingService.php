<?php

namespace App\Services\Inventory;

use App\Models\Income;
use App\Models\Expense;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Inventory\SalePayment;
use App\Models\Inventory\PurchasePayment;
use App\Models\Inventory\InternalConsumption;

class AccountingService
{
    // ══════════════════════════════════════════════════════════════
    // SALE PAYMENT → Income
    // ══════════════════════════════════════════════════════════════

    public function createSaleIncome(SalePayment $payment): void
    {
        $sale     = $payment->sale;
        $category = IncomeCategory::where('slug', 'product_sale')->first();

        Income::create([
            'category_id'    => $category->id,
            'amount'         => $payment->amount,
            'income_date'    => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'customer_id'    => $sale->client_id ?? null,
            'payer'          => $sale->customer_name,
            'reference_no'   => $sale->invoice_no,
            'description'    => 'Product Sale Payment — ' . $sale->sale_no,
            'status'         => 'active',
            'created_by'     => auth()->id(),
        ]);
    }

    public function voidSaleIncome(SalePayment $payment): void
    {
        Income::where('reference_no', $payment->sale->invoice_no)
              ->where('amount', $payment->amount)
              ->where('status', 'active')
              ->update([
                  'status'      => 'void',
                  'void_reason' => 'Sale Payment Void — ' . $payment->sale->sale_no,
              ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PURCHASE PAYMENT → Expense
    // ══════════════════════════════════════════════════════════════

    public function createPurchaseExpense(PurchasePayment $payment): void
    {
        $purchase = $payment->purchase;
        $category = ExpenseCategory::where('slug', 'stock_purchase')->first();

        Expense::create([
            'category_id'    => $category->id,
            'amount'         => $payment->amount,
            'expense_date'   => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'payee'          => $purchase->vendor->name,
            'reference_no'   => $purchase->purchase_no,
            'description'    => 'Stock Purchase Payment — ' . $purchase->purchase_no,
            'status'         => 'approved',
            'created_by'     => auth()->id(),
        ]);
    }

    public function voidPurchaseExpense(PurchasePayment $payment): void
    {
        Expense::where('reference_no', $payment->purchase->purchase_no)
               ->where('amount', $payment->amount)
               ->where('status', 'approved')
               ->update([
                   'status'        => 'void',
                   'reject_reason' => 'Purchase Payment Void — ' . $payment->purchase->purchase_no,
               ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CONSUMPTION → Expense
    // ══════════════════════════════════════════════════════════════

    public function createConsumptionExpense(InternalConsumption $consumption): void
    {
        $category = ExpenseCategory::where('slug', 'consumption_expense')->first();

        $description = 'Internal Consumption — ' . $consumption->purpose;
        if ($consumption->reference_note) {
            $description .= ' (' . $consumption->reference_note . ')';
        }

        Expense::create([
            'category_id'    => $category->id,
            'amount'         => $consumption->total_amount,
            'expense_date'   => $consumption->consumption_date,
            'payment_method' => 'cash',
            'payee'          => 'Internal Use',
            'reference_no'   => $consumption->consumption_no,
            'description'    => $description,
            'status'         => 'approved',
            'created_by'     => auth()->id(),
        ]);
    }

    public function voidConsumptionExpense(InternalConsumption $consumption): void
    {
        Expense::where('reference_no', $consumption->consumption_no)
               ->where('status', 'approved')
               ->update([
                   'status'        => 'void',
                   'reject_reason' => 'Consumption Void — ' . $consumption->consumption_no,
               ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SALE RETURN → Income Minus
    // ══════════════════════════════════════════════════════════════

    public function createSaleReturnIncome(\App\Models\Inventory\SaleReturn $return): void
    {
        $category = IncomeCategory::where('slug', 'sale_return')->first();

        Income::create([
            'category_id'    => $category->id,
            'amount'         => $return->total_amount,
            'income_date'    => $return->return_date,
            'payment_method' => 'cash',
            'customer_id'    => $return->client_id ?? null,
            'payer'          => $return->client?->name ?? 'Walk-in',
            'reference_no'   => $return->return_no,
            'description'    => 'Sale Return — ' . $return->sale->sale_no,
            'status'         => 'void', // income কমানোর জন্য void হিসাবে
            'void_reason'    => 'Sale Return Approved',
            'created_by'     => auth()->id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PURCHASE RETURN → Expense Minus
    // ══════════════════════════════════════════════════════════════

    public function createPurchaseReturnExpense(\App\Models\Inventory\PurchaseReturn $return): void
    {
        $category = ExpenseCategory::where('slug', 'purchase_return')->first();

        Expense::create([
            'category_id'    => $category->id,
            'amount'         => $return->total_amount,
            'expense_date'   => $return->return_date,
            'payment_method' => 'cash',
            'payee'          => $return->vendor->name,
            'reference_no'   => $return->return_no,
            'description'    => 'Purchase Return — ' . $return->purchase->purchase_no,
            'status'         => 'void', // expense কমানোর জন্য void হিসাবে
            'reject_reason'  => 'Purchase Return Approved',
            'created_by'     => auth()->id(),
        ]);
    }
}
