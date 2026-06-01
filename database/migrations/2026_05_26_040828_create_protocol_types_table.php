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
    if (!Schema::hasTable('protocol_types')) {
        Schema::create('protocol_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    Schema::table('customers', function (Blueprint $table) {
        if (!Schema::hasColumn('customers', 'protocol_type_id')) {
            $table->unsignedBigInteger('protocol_type_id')->nullable();
            $table->foreign('protocol_type_id')->references('id')->on('protocol_types')->nullOnDelete();
        }
    });
}
};
