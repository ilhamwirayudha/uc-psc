<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('id')->constrained('bookings')->nullOnDelete();
            $table->enum('method', ['offline', 'online'])->default('offline')->after('tested_at');
            $table->enum('status', [
                'scheduled',
                'test_completed',
                'waiting_assessment',
                'assigned',
                'in_review',
                'revision',
                'review_completed',
                'result_ready',
                'result_sent',
                'completed',
            ])->default('scheduled')->after('method');
            $table->timestamp('assigned_at')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('assigned_at');
            $table->timestamp('completed_at')->nullable()->after('reviewed_at');
            $table->date('result_due_date')->nullable()->after('completed_at');
            $table->enum('delivery_method', ['whatsapp', 'email', 'offline'])->nullable()->after('result_due_date');
            $table->timestamp('delivered_at')->nullable()->after('delivery_method');
            $table->foreignId('delivered_by')->nullable()->after('delivered_at')->constrained('users')->nullOnDelete();
            $table->text('revision_notes')->nullable()->after('result_summary');
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['delivered_by']);
            $table->dropColumn([
                'booking_id',
                'method',
                'status',
                'assigned_at',
                'reviewed_at',
                'completed_at',
                'result_due_date',
                'delivery_method',
                'delivered_at',
                'delivered_by',
                'revision_notes',
            ]);
        });
    }
};
