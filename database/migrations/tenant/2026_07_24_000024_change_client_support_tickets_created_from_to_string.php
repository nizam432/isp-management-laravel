<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * client_support_tickets.created_from was created as an enum (likely
 * enum('admin','client')) — but the Reseller portal now needs to save
 * 'reseller' as a value, which isn't in that list. Any value not in the
 * enum causes "Data truncated for column 'created_from'" and the insert
 * fails. Converting to a plain string removes this recurring failure mode
 * — no more migration needed every time a new source is introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_support_tickets', function (Blueprint $table) {
            $table->string('created_from', 50)->default('admin')->change();
        });
    }

    public function down(): void
    {
        Schema::table('client_support_tickets', function (Blueprint $table) {
            $table->enum('created_from', ['admin', 'client'])->default('admin')->change();
        });
    }
};
