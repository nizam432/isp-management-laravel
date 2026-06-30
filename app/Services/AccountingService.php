<?php

namespace App\Services\Inventory;

use App\Models\Income;
use App\Models\Expense;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Inventory\SalePayment;
use App\Models\Inventory\PurchasePayment;
use App\Models\Inventory\InternalConsumption;
use App\Models\Inventory\SaleReturn;
use App\Models\Inventory\PurchaseReturn;

class AccountingService
{
    // ══════════════════════════════════════════════════════════════
    // SALE PAYMENT → Income
    // ══════════════════════════════════════════════════════════════

    public function createSaleIncome(SalePayment $payment): ?Income
    {
        $sale     = $payment->sale;
        $category = IncomeCategory::where('slug', 'product_sale')
            ->orWhere('slug', 'product-sale')
            ->first();
        if (! $category) return null;

        return Income::create([
            'income_no'         => Income::generateNumber(),
            'category_id'       => $category->id,
            'amount'            => $payment->amount,
            'income_date'       => $payment->payment_date,
            'payment_method'    => $payment->payment_method,
            'customer_id'       => $sale->client_id ?? null,
            'payer'             => $sale->customer_name,
            'reference_no'      => $sale->invoice_no,
            'description'       => "Product Sale: {$sale->sale_no}"
                                   . " [Payment: ৳" . number_format($payment->amount, 2) . "]",
            'source_type'       => 'product_sale',
            'source_id'         => $payment->id,
            'source_invoice_id' => $sale->id,
            'status'            => 'active',
            'created_by'        => auth()->id(),
        ]);
    }

    public function voidSaleIncome(SalePayment $payment): void
    {
        Income::where('source_type', 'product_sale')
              ->where('source_id', $payment->id)
              ->where('status', 'active')
              ->update([
                  'status'      => 'void',
                  'void_reason' => 'Sale Payment Void — ' . $payment->sale->sale_no,
                  'void_date'   => now(),
                  'void_by'     => auth()->id(),
              ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PURCHASE PAYMENT → Expense
    // ══════════════════════════════════════════════════════════════

    public function createPurchaseExpense(PurchasePayment $payment): ?Expense
    {
        $purchase = $payment->purchase;
        $category = ExpenseCategory::where('slug', 'stock_purchase')->first();
        if (! $category) return null;

        return Expense::create([
            'expense_no'        => Expense::generateNumber(),
            'category_id'       => $category->id,
            'amount'            => $payment->amount,
            'expense_date'      => $payment->payment_date,
            'payment_method'    => $payment->payment_method,
            'payee'             => $purchase->vendor->name,
            'reference_no'      => $purchase->purchase_no,
            'description'       => "Stock Purchase: {$purchase->purchase_no}"
                                   . " [Payment: ৳" . number_format($payment->amount, 2) . "]",
            'source_type'       => 'inventory_purchase',
            'source_id'         => $payment->id,
            'source_invoice_id' => $purchase->id,
            'status'            => 'approved',
            'created_by'        => auth()->id(),
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);
    }

    public function voidPurchaseExpense(PurchasePayment $payment): void
    {
        Expense::where('source_type', 'inventory_purchase')
               ->where('source_id', $payment->id)
               ->where('status', 'approved')
               ->update([
                   'status'        => 'void',
                   'reject_reason' => 'Purchase Payment Void — ' . $payment->purchase->purchase_no,
                   'void_date'     => now(),
                   'void_by'       => auth()->id(),
               ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CONSUMPTION → Expense
    // ══════════════════════════════════════════════════════════════

    public function createConsumptionExpense(InternalConsumption $consumption): ?Expense
    {
        $category = ExpenseCategory::where('slug', 'consumption_expense')->first();
        if (! $category) return null;

        $description = 'Internal Consumption — ' . $consumption->purpose;
        if ($consumption->reference_note) {
            $description .= ' (' . $consumption->reference_note . ')';
        }

        return Expense::create([
            'expense_no'        => Expense::generateNumber(),
            'category_id'       => $category->id,
            'amount'            => $consumption->total_amount,
            'expense_date'      => $consumption->consumption_date,
            'payment_method'    => 'cash',
            'payee'             => 'Internal Use',
            'reference_no'      => $consumption->consumption_no,
            'description'       => $description,
            'source_type'       => 'inventory_consumption',
            'source_id'         => $consumption->id,
            'source_invoice_id' => $consumption->id,
            'status'            => 'approved',
            'created_by'        => auth()->id(),
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);
    }

    public function voidConsumptionExpense(InternalConsumption $consumption): void
    {
        Expense::where('source_type', 'inventory_consumption')
               ->where('source_id', $consumption->id)
               ->where('status', 'approved')
               ->update([
                   'status'        => 'void',
                   'reject_reason' => 'Consumption Void — ' . $consumption->consumption_no,
                   'void_date'     => now(),
                   'void_by'       => auth()->id(),
               ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SALE RETURN → Negative Income Entry (active, amount negative)
    // ══════════════════════════════════════════════════════════════

    public function createSaleReturnIncome(SaleReturn $return): ?Income
    {
        $category = IncomeCategory::where('slug', 'product-return')
            ->orWhere('slug', 'product_return')
            ->first();
        if (! $category) return null;

        $sale = $return->sale;

        // ── Income এ শুধু ততটুকুই বিয়োগ হবে যতটুকু আসলে Received হয়েছিল ──
        // ইতিমধ্যে কত টাকা Income এ negative entry হয়ে গেছে (আগের return গুলো থেকে)
        $alreadyDeducted = Income::where('source_type', 'product_return')
            ->where('source_invoice_id', $sale->id)
            ->where('status', 'active')
            ->sum('amount'); // negative sum

        $alreadyDeducted = abs($alreadyDeducted);
        $totalPaid       = (float) $sale->paid_amount; // sale already reduced বা আগের state — call site এ আগে capture করা ভালো

        $availableToDeduct = max(0, $totalPaid - $alreadyDeducted);
        $incomeImpact       = min($return->total_amount, $availableToDeduct);

        if ($incomeImpact <= 0) {
            return null; // কোনো টাকা Income এ আসেইনি — কিছু বিয়োগ করার নেই
        }

        return Income::create([
            'income_no'         => Income::generateNumber(),
            'category_id'       => $category->id,
            'amount'            => -abs($incomeImpact), // শুধু received অংশ negative
            'income_date'       => $return->return_date,
            'payment_method'    => 'cash',
            'customer_id'       => $return->client_id ?? null,
            'payer'             => $return->client?->name ?? 'Walk-in',
            'reference_no'      => $return->return_no,
            'description'       => "Sale Return — {$sale->sale_no}"
                                   . " [Return Item Value: ৳" . number_format($return->total_amount, 2)
                                   . " | Income Adjusted: ৳" . number_format($incomeImpact, 2) . "]",
            'source_type'       => 'product_return',
            'source_id'         => $return->id,
            'source_invoice_id' => $sale->id,
            'status'            => 'active',
            'created_by'        => auth()->id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PURCHASE RETURN → Negative Expense Entry (active, amount negative)
    // ══════════════════════════════════════════════════════════════

    public function createPurchaseReturnExpense(PurchaseReturn $return): ?Expense
    {
        $category = ExpenseCategory::where('slug', 'purchase_return')
            ->orWhere('slug', 'purchase-return')
            ->first();
        if (! $category) return null;

        $purchase = $return->purchase;

        // ── Expense এ শুধু ততটুকুই বিয়োগ হবে যতটুকু আসলে Paid হয়েছিল ──
        $alreadyDeducted = Expense::where('source_type', 'purchase_return')
            ->where('source_invoice_id', $purchase->id)
            ->where('status', '!=', 'void')
            ->sum('amount'); // negative sum

        $alreadyDeducted = abs($alreadyDeducted);
        $totalPaid       = (float) $purchase->paid_amount;

        $availableToDeduct = max(0, $totalPaid - $alreadyDeducted);
        $expenseImpact      = min($return->total_amount, $availableToDeduct);

        if ($expenseImpact <= 0) {
            return null; // কোনো টাকা Expense এ আসেইনি — কিছু বিয়োগ করার নেই
        }

        return Expense::create([
            'expense_no'        => Expense::generateNumber(),
            'category_id'       => $category->id,
            'amount'            => -abs($expenseImpact), // শুধু paid অংশ negative
            'expense_date'      => $return->return_date,
            'payment_method'    => 'cash',
            'payee'             => $return->vendor->name ?? '—',
            'reference_no'      => $return->return_no,
            'description'       => "Purchase Return — {$purchase->purchase_no}"
                                   . " [Return Item Value: ৳" . number_format($return->total_amount, 2)
                                   . " | Expense Adjusted: ৳" . number_format($expenseImpact, 2) . "]",
            'source_type'       => 'purchase_return',
            'source_id'         => $return->id,
            'source_invoice_id' => $purchase->id,
            'status'            => 'approved',
            'created_by'        => auth()->id(),
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);
    }
}
