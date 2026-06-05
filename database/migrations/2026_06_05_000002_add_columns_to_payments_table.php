<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //$table->enum('status', ['active', 'void'])->default('active')->after('remarks');
            //$table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->string('receive_from', 100)->nullable()->after('received_by');
            $table->boolean('send_sms')->default(0)->after('receive_from');
            $table->boolean('set_next_billing_date')->default(0)->after('send_sms');
            $table->date('payment_date')->nullable()->after('set_next_billing_date');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['status', 'received_by', 'receive_from', 'send_sms', 'set_next_billing_date', 'payment_date']);
        });
    }
};
