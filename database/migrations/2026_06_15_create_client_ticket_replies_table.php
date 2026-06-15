<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('client_support_tickets')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // admin reply
            $table->text('message');
            $table->string('attachment', 255)->nullable();
            $table->enum('sender_type', ['customer', 'admin'])->default('customer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_ticket_replies');
    }
};
