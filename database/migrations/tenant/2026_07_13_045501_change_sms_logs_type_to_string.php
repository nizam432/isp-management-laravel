<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * sms_logs.type was created as enum('bill_reminder','payment_confirm',
 * 'expiry','welcome','custom') — but SmsService actually uses different
 * type strings in practice ('bill_due', 'invoice_generated', 'suspend',
 * 'restore', etc.), none of which match this enum's allowed values. Any
 * type not in the list causes "Data truncated for column 'type'" and the
 * whole SMS log insert fails. Converting to a plain string removes this
 * recurring failure mode — no more migration needed every time a new type
 * is introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->string('type', 50)->default('custom')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->enum('type', ['bill_reminder', 'payment_confirm', 'expiry', 'welcome', 'custom'])
                  ->default('custom')->change();
        });
    }
};