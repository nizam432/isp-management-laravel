<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removed the `Schema::table('customers', ...)` block that was here —
     * it added `protocol_type_id` to customers, but customers' own migration
     * ALREADY defines this exact column directly
     * ($table->foreignId('protocol_type_id')->nullable()->constrained('protocol_types')).
     * That block was 100% redundant (its own hasColumn guard would always
     * skip it once customers exists) and only caused failures when this
     * file ran BEFORE customers existed yet, which it must (customers
     * depends on protocol_types).
     */
    public function up(): void
    {
        if (!Schema::hasTable('protocol_types')) {
            Schema::create('protocol_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('details')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_types');
    }
};
