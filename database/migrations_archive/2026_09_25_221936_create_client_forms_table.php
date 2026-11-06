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
        Schema::create('client_forms', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('form_type'); // 'anak', 'dewasa', 'pra_nikah', 'pernikahan', 'industri', 'non_industri', 'biography_en'
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_phone');
            $table->boolean('consent_agreed')->default(true);
            $table->timestamp('consent_agreed_at')->nullable();
            $table->string('service_preference')->default('offline'); // 'online', 'offline'
            $table->string('status')->default('baru'); // 'baru', 'diproses', 'selesai', 'dibatalkan'
            $table->json('answers')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_forms');
    }
};
