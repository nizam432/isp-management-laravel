<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // ── Recipient (polymorphic) ──────────────────
            // যেকোনো model (User, Employee, ভবিষ্যতে অন্য কিছু) notification পেতে পারবে
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type');
            $table->index(['notifiable_type', 'notifiable_id']);

            // ── Sender (optional — কে পাঠালো) ────────────
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_type')->nullable();

            // ── Content ───────────────────────────────────
            $table->string('type')->default('general'); // general, ticket, system, etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();          // font-awesome class, e.g. fa-ticket-alt
            $table->string('color')->default('primary');  // badge/icon color
            $table->string('url')->nullable();             // click করলে কোথায় যাবে

            // ── Polymorphic related model (optional) ─────
            // যেমন notification টা কোন Ticket নিয়ে — related_type=ClientSupportTicket, related_id=5
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
