<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private SmsService $sms;

    public function __construct()
    {
        $this->sms = new SmsService();
    }

    // ── Payment Confirmation ──────────────────────────────

    public function paymentConfirm(Customer $customer, float $amount, string $method): void
    {
        if (Setting::get('payment_confirm_sms', '1') == '1') {
            $this->sms->sendPaymentConfirm(
                $customer->phone,
                $customer->name,
                $amount,
                strtoupper($method)
            );
        }

        if (Setting::get('payment_confirm_email', '0') == '1') {
            $this->sendEmail($customer, 'Payment Confirmation', 'emails.payment_confirm', [
                'customer' => $customer,
                'amount'   => $amount,
                'method'   => $method,
            ]);
        }
    }

    // ── Account Created ───────────────────────────────────

    public function accountCreated(Customer $customer, string $username, string $password): void
    {
        if (Setting::get('account_created_sms', '1') == '1') {
            $this->sms->sendWelcome(
                $customer->phone,
                $customer->name,
                $username,
                $password
            );
        }

        if (Setting::get('account_created_email', '0') == '1') {
            $this->sendEmail($customer, 'Welcome to ' . Setting::get('company_name', 'ISP'), 'emails.account_created', [
                'customer' => $customer,
                'username' => $username,
                'password' => $password,
            ]);
        }
    }

    // ── Invoice Generated ─────────────────────────────────

    public function invoiceGenerated(Customer $customer, Invoice $invoice): void
    {
        if (Setting::get('invoice_generated_sms', '1') == '1') {
            $message = "Dear {$customer->name}, your invoice {$invoice->invoice_no} for {$invoice->period_label} has been generated. Amount: {$invoice->due_amount} BDT. Due: {$invoice->due_date?->format('d M Y')}.";
            $this->sms->send($customer->phone, $message, 'invoice_generated');
        }

        if (Setting::get('invoice_generated_email', '0') == '1') {
            $this->sendEmail($customer, 'Invoice Generated - ' . $invoice->invoice_no, 'emails.invoice_generated', [
                'customer' => $customer,
                'invoice'  => $invoice,
            ]);
        }
    }

    // ── Bill Due Reminder ─────────────────────────────────

    public function billDueReminder(Customer $customer, Invoice $invoice): void
    {
        if (Setting::get('bill_due_sms', '1') == '1') {
            $this->sms->sendBillDue(
                $customer->phone,
                $customer->name,
                $invoice->due_amount,
                $invoice->period_label
            );
        }

        if (Setting::get('bill_due_email', '0') == '1') {
            $this->sendEmail($customer, 'Bill Due Reminder - ' . $invoice->invoice_no, 'emails.bill_due', [
                'customer' => $customer,
                'invoice'  => $invoice,
            ]);
        }
    }

    // ── Expiry Reminder ───────────────────────────────────

    public function expiryReminder(Customer $customer): void
    {
        $expiry = $customer->expire_date;
        if (!$expiry) return;

        if (Setting::get('expiry_sms', '1') == '1') {
            $message = "Dear {$customer->name}, your internet connection will expire on {$expiry->format('d M Y')}. Please renew to avoid interruption.";
            $this->sms->send($customer->phone, $message, 'expiry_reminder');
        }

        if (Setting::get('expiry_email', '0') == '1') {
            $this->sendEmail($customer, 'Connection Expiry Reminder', 'emails.expiry_reminder', [
                'customer' => $customer,
                'expiry'   => $expiry,
            ]);
        }
    }

    // ── Payment Overdue ───────────────────────────────────

    public function paymentOverdue(Customer $customer, Invoice $invoice): void
    {
        if (Setting::get('overdue_sms', '1') == '1') {
            $message = "Dear {$customer->name}, your invoice {$invoice->invoice_no} is overdue. Due amount: {$invoice->due_amount} BDT. Please pay immediately.";
            $this->sms->send($customer->phone, $message, 'overdue');
        }

        if (Setting::get('overdue_email', '0') == '1') {
            $this->sendEmail($customer, 'Payment Overdue - ' . $invoice->invoice_no, 'emails.overdue', [
                'customer' => $customer,
                'invoice'  => $invoice,
            ]);
        }
    }

    // ── Suspension ────────────────────────────────────────

    public function suspended(Customer $customer): void
    {
        if (Setting::get('suspension_sms', '1') == '1') {
            $this->sms->sendSuspendNotice($customer->phone, $customer->name);
        }

        if (Setting::get('suspension_email', '0') == '1') {
            $this->sendEmail($customer, 'Connection Suspended', 'emails.suspended', [
                'customer' => $customer,
            ]);
        }
    }

    // ── Connection Restored ───────────────────────────────

    public function restored(Customer $customer): void
    {
        if (Setting::get('restore_sms', '1') == '1') {
            $this->sms->sendRestoreNotice($customer->phone, $customer->name);
        }

        if (Setting::get('restore_email', '0') == '1') {
            $this->sendEmail($customer, 'Connection Restored', 'emails.restored', [
                'customer' => $customer,
            ]);
        }
    }

    // ── Package Changed ───────────────────────────────────

    public function packageChanged(Customer $customer, string $oldPackage, string $newPackage): void
    {
        if (Setting::get('package_changed_sms', '1') == '1') {
            $message = "Dear {$customer->name}, your internet package has been changed from {$oldPackage} to {$newPackage}.";
            $this->sms->send($customer->phone, $message, 'package_changed');
        }

        if (Setting::get('package_changed_email', '0') == '1') {
            $this->sendEmail($customer, 'Package Changed', 'emails.package_changed', [
                'customer'   => $customer,
                'oldPackage' => $oldPackage,
                'newPackage' => $newPackage,
            ]);
        }
    }

    // ── Password Reset ────────────────────────────────────

    public function passwordReset(Customer $customer, string $newPassword): void
    {
        if (Setting::get('password_reset_sms', '1') == '1') {
            $message = "Dear {$customer->name}, your portal password has been reset. New password: {$newPassword}. Please change it after login.";
            $this->sms->send($customer->phone, $message, 'password_reset');
        }

        if (Setting::get('password_reset_email', '0') == '1') {
            $this->sendEmail($customer, 'Password Reset', 'emails.password_reset', [
                'customer'    => $customer,
                'newPassword' => $newPassword,
            ]);
        }
    }

    // ── Private: Send Email ───────────────────────────────

    private function sendEmail(Customer $customer, string $subject, string $view, array $data): void
    {
        // Skip if no email
        if (empty($customer->email)) return;

        // Skip if company email not configured
        $fromEmail = Setting::get('company_email', '');
        $fromName  = Setting::get('company_name', config('app.name'));

        if (empty($fromEmail)) return;

        try {
            Mail::send($view, $data, function ($mail) use ($customer, $subject, $fromEmail, $fromName) {
                $mail->to($customer->email, $customer->name)
                     ->from($fromEmail, $fromName)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Email failed to ' . $customer->email . ': ' . $e->getMessage());
        }
    }
}
