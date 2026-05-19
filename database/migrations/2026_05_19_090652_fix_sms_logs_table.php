<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('sms_logs', function (Blueprint $table) {
        if (!Schema::hasColumn('sms_logs', 'gateway')) {
            $table->string('gateway', 50)->after('id');
        }
        if (!Schema::hasColumn('sms_logs', 'mobile')) {
            $table->string('mobile', 20)->after('gateway');
        }
        if (!Schema::hasColumn('sms_logs', 'type')) {
            $table->string('type', 50)->default('general');
        }
        if (!Schema::hasColumn('sms_logs', 'response')) {
            $table->text('response')->nullable();
        }
    });
}

public function down(): void {}
};
