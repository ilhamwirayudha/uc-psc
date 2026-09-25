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
        Schema::dropIfExists('counseling_records');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not needed for feature removal, but good practice
        Schema::create('counseling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type'); // Individual, Couple, Family, dll.
            $table->text('notes')->nullable();
            $table->string('status')->default('Selesai');
            $table->timestamps();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
