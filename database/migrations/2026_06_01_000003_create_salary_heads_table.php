<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salary_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['addition', 'deduction'])->default('addition');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salary_heads'); }
};
