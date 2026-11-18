<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('client_forms', 'booking_status')) {
                $table->string('booking_status', 40)->default('belum_dibooking')->after('service_preference');
            }
            if (!Schema::hasColumn('client_forms', 'company_name')) {
                $table->string('company_name')->nullable()->after('client_phone');
            }
        });

        DB::table('client_forms')
            ->whereNotNull('booking_id')
            ->update(['booking_status' => 'sudah_dibooking']);

        if (Schema::hasColumn('clients', 'assigned_staff_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_staff_id');
            });
        }

        Schema::dropIfExists('client_staff_assignments');
    }

    public function down(): void
    {
        if (!Schema::hasTable('client_staff_assignments')) {
            Schema::create('client_staff_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('assigned_at');
                $table->timestamp('ended_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('clients', 'assigned_staff_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        Schema::table('client_forms', function (Blueprint $table) {
            $cols = array_values(array_filter(['booking_status', 'company_name'], fn ($c) => Schema::hasColumn('client_forms', $c)));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
