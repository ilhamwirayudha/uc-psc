<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->enum('service_type', ['konseling', 'psikotes'])->default('konseling')->after('source');
            $table->string('counseling_type')->nullable()->after('service_type');
            $table->enum('gender', ['l', 'p'])->nullable()->after('email');
            $table->date('dob')->nullable()->after('gender');
            
            // Drop old status and create new one to avoid enum conflicts
            $table->dropColumn('status');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->enum('status', [
                'unassigned',
                'assigned',
                'ongoing',
                'unpaid',
                'paid',
                'needs_followup',
                'completed'
            ])->default('unassigned')->after('counseling_type');
        });

        Schema::table('counseling_records', function (Blueprint $table) {
            $table->dateTime('end_time')->nullable()->after('scheduled_at');
            $table->string('location')->nullable()->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'counseling_type', 'gender', 'dob']);
            $table->dropColumn('status');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->enum('status', [
                'unassigned',
                'assigned',
                'unpaid',
                'paid',
                'upcoming',
                'ongoing',
                'completed'
            ])->default('unassigned');
        });

        Schema::table('counseling_records', function (Blueprint $table) {
            $table->dropColumn(['end_time', 'location']);
        });
    }
};
