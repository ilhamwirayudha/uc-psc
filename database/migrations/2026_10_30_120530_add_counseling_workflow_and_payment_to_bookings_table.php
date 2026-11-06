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
        Schema::table('bookings', function (Blueprint $table) {
            // Ubah tipe kolom status menjadi string agar fleksibel menampung alur status baru
            $table->string('status', 50)->default('data_masuk')->change();

            // Field Durasi & Overtime Sesi Aktual
            $table->unsignedSmallInteger('actual_duration_minutes')->default(0)->after('overtime_minutes');
            $table->boolean('is_overtime')->default(false)->after('actual_duration_minutes');

            // Field Alur Pembayaran UC Finance
            $table->string('payment_workflow_status', 60)->default('menunggu_sesi_selesai')->after('is_overtime');
            $table->string('va_number', 50)->nullable()->after('payment_workflow_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'actual_duration_minutes',
                'is_overtime',
                'payment_workflow_status',
                'va_number',
            ]);
        });
    }
};
