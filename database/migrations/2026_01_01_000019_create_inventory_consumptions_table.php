<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_consumptions', function (Blueprint $table) {
            $table->id();
            $table->string('consumption_no')->unique();
            $table->date('consumption_date');
            $table->foreignId('location_id')->constrained('inventory_store_locations')->restrictOnDelete();
            $table->string('purpose')->comment('Installation, Maintenance, Office Use etc');
            $table->string('reference_note')->nullable()->comment('যেমন: Mirpur Zone, Block-A');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->boolean('is_void')->default(false);
            $table->text('void_reason')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['consumption_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_consumptions');
    }
};
