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
        // 1. Counselors
        Schema::create('counselors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('country')->default('Indonesia')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('siap_number')->nullable();
            $table->date('siap_valid_until')->nullable();
            $table->string('sipp_number')->nullable();
            $table->date('sipp_valid_until')->nullable();
            $table->string('specialization')->nullable();
            $table->string('str_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Payment Transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('jumlah', 12, 2);
            $table->string('metode')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('tanggal')->nullable();
            $table->timestamps();
        });

        // 3. Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pic_name')->nullable();
            $table->string('jenis', 50)->default('individual');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('address')->nullable();
            $table->enum('source', ['whatsapp', 'walk_in', 'referral', 'other'])->default('whatsapp');
            $table->string('service_type')->nullable();
            $table->string('counseling_type')->nullable();
            $table->enum('status', ['unassigned', 'assigned', 'ongoing', 'unpaid', 'paid', 'needs_followup', 'completed'])->default('unassigned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Client Participants (Peserta Klien)
        Schema::create('client_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('nama_peserta');
            $table->timestamps();
        });

        // 5. Client Staff Assignments
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

        // 6. Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->enum('kategori', ['konseling', 'psikotes']);
            $table->date('tanggal_booking_dibuat');
            $table->date('tanggal_dijadwalkan')->nullable();
            $table->time('session_start')->nullable();
            $table->time('session_end')->nullable();
            $table->unsignedSmallInteger('session_duration_standard')->default(60);
            $table->unsignedSmallInteger('overtime_minutes')->default(0);
            $table->text('session_notes')->nullable();
            $table->enum('session_type', ['tatap_muka', 'online', 'whatsapp'])->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['belum_assign', 'baru', 'lanjutan', 'selesai'])->default('belum_assign');
            $table->foreignId('staff_penguji_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_koreksi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_pelapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->foreignId('follow_up_of_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Booking Participants
        Schema::create('booking_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('nama_peserta');
            $table->timestamps();
        });

        // 8. Client Forms
        Schema::create('client_forms', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number');
            $table->string('form_type');
            $table->foreignId('client_id')->nullable()->constrained('clients')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_phone');
            $table->boolean('consent_agreed')->default(true);
            $table->timestamp('consent_agreed_at')->nullable();
            $table->string('service_preference')->default('offline');
            $table->string('status')->default('baru');
            $table->json('answers')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 9. Pairings
        Schema::create('pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('counselors')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Test Results
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('test_name');
            $table->text('result_summary')->nullable();
            $table->text('revision_notes')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('administered_by')->constrained('users');
            $table->date('tested_at')->nullable();
            $table->enum('method', ['offline', 'online'])->default('offline');
            $table->enum('status', [
                'scheduled', 'test_completed', 'waiting_assessment', 'assigned',
                'in_review', 'revision', 'review_completed', 'result_ready',
                'result_sent', 'completed'
            ])->default('scheduled');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('result_due_date')->nullable();
            $table->enum('delivery_method', ['whatsapp', 'email', 'offline'])->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Test Result Documents
        Schema::create('test_result_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained('test_results')->cascadeOnDelete();
            $table->enum('document_type', [
                'test_form', 'answer_sheet', 'scoring', 'psychological_report',
                'supporting_document', 'final_result'
            ])->default('final_result');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Test Result Activities
        Schema::create('test_result_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained('test_results')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description');
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_result_activities');
        Schema::dropIfExists('test_result_documents');
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('pairings');
        Schema::dropIfExists('client_forms');
        Schema::dropIfExists('booking_participants');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('client_staff_assignments');
        Schema::dropIfExists('client_participants');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('counselors');
    }
};
