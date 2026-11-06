<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('kategori', ['konseling', 'psikotes']);
            $table->date('tanggal_booking_dibuat');
            $table->date('tanggal_dijadwalkan')->nullable();
            $table->enum('status', ['belum_assign', 'baru', 'lanjutan', 'selesai'])->default('belum_assign');
            $table->foreignId('staff_penguji_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_koreksi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_pelapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->foreignId('follow_up_of_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
