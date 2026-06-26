<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('degree', 100);
            $table->string('institution', 100)->nullable();
            $table->string('passing_year', 10)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_educations'); }
};
