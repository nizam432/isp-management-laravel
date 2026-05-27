<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 20)->unique()->comment('e.g. ISP-0001');
            $table->string('name', 100);
            $table->string('phone', 20)->index();
            $table->string('email', 150)->nullable();
            $table->string('nid_number', 30)->nullable();
            $table->string('nid_photo', 255)->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('area', 100)->nullable();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->date('connection_date')->nullable();
            $table->tinyInteger('billing_date')->default(1)->comment('day of month 1-28');
            $table->enum('status', ['active', 'inactive', 'suspended', 'expired'])->default('active');
            $table->string('ip_address', 20)->nullable();
            $table->string('mac_address', 20)->nullable();
            $table->string('pppoe_username', 50)->nullable();
            $table->string('pppoe_password', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
