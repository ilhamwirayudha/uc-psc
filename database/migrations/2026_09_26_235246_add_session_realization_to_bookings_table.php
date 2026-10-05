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
            $table->time('session_start')->nullable()->after('tanggal_dijadwalkan');
            $table->time('session_end')->nullable()->after('session_start');
            $table->unsignedSmallInteger('session_duration_standard')->default(60)->after('session_end');
            $table->unsignedSmallInteger('overtime_minutes')->default(0)->after('session_duration_standard');
            $table->text('session_notes')->nullable()->after('overtime_minutes');
            $table->enum('session_type', ['tatap_muka', 'online', 'whatsapp'])->nullable()->after('session_notes');
            $table->string('location')->nullable()->after('session_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'session_start', 'session_end', 'session_duration_standard',
                'overtime_minutes', 'session_notes', 'session_type', 'location',
            ]);
        });
    }
};
