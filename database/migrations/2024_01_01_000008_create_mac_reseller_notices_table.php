<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_reseller_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->nullable()->constrained('mac_resellers')->nullOnDelete();
            $table->string('title');
            $table->longText('details');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_reseller_notices');
    }
};
