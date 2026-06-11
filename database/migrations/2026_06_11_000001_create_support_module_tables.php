<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Support Categories ─────────────────────────────────
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->enum('category_type', ['for_everyone', 'only_for_office'])->default('for_everyone');
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Client Support Tickets ─────────────────────────────
        Schema::create('client_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('support_category_id')->nullable()->constrained('support_categories')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'processing', 'solved', 'closed'])->default('pending');
            $table->string('complained_no', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->boolean('send_sms')->default(false);
            $table->enum('created_from', ['admin', 'client'])->default('admin');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('solved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('solved_at')->nullable();
            $table->timestamps();
        });

        // ── Ticket Assignees (multiple employees per ticket) ───
        Schema::create('ticket_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('client_support_tickets')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assignees');
        Schema::dropIfExists('client_support_tickets');
        Schema::dropIfExists('support_categories');
    }
};
