<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'rescheduled_by')) {
                    $table->string('rescheduled_by', 30)->nullable()->after('rescheduled_at');
                }
                if (!Schema::hasColumn('bookings', 'overtime_units')) {
                    $table->unsignedSmallInteger('overtime_units')->default(0)->after('overtime_minutes');
                }
            });
        }

        if (Schema::hasTable('booking_candidates')) {
            Schema::table('booking_candidates', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_candidates', 'softcopy_sent_at')) {
                    $table->timestamp('softcopy_sent_at')->nullable()->after('step_kirim_by');
                }
                if (!Schema::hasColumn('booking_candidates', 'softcopy_sent_by')) {
                    $table->foreignId('softcopy_sent_by')->nullable()->after('softcopy_sent_at')
                        ->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_candidates')) {
            Schema::table('booking_candidates', function (Blueprint $table) {
                if (Schema::hasColumn('booking_candidates', 'softcopy_sent_by')) {
                    $table->dropConstrainedForeignId('softcopy_sent_by');
                }
                if (Schema::hasColumn('booking_candidates', 'softcopy_sent_at')) {
                    $table->dropColumn('softcopy_sent_at');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $cols = array_values(array_filter([
                    Schema::hasColumn('bookings', 'rescheduled_by') ? 'rescheduled_by' : null,
                    Schema::hasColumn('bookings', 'overtime_units') ? 'overtime_units' : null,
                ]));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
