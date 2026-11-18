<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('client_id')->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('bookings', 'billing_scheme')) {
                $table->string('billing_scheme', 20)->default('v')->after('overtime_price'); // v | I | R | -
            }
            if (!Schema::hasColumn('bookings', 'requires_outgoing_letter')) {
                $table->boolean('requires_outgoing_letter')->default(false)->after('billing_scheme');
            }
            if (!Schema::hasColumn('bookings', 'delivery_mode')) {
                $table->string('delivery_mode', 20)->default('softcopy')->after('requires_outgoing_letter'); // softcopy | hardcopy | both
            }
            if (!Schema::hasColumn('bookings', 'final_price')) {
                $table->decimal('final_price', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('bookings', 'finance_invoice_number')) {
                $table->string('finance_invoice_number', 100)->nullable()->after('va_number');
            }
            if (!Schema::hasColumn('bookings', 'payment_proof_notes')) {
                $table->text('payment_proof_notes')->nullable()->after('finance_invoice_number');
            }
            if (!Schema::hasColumn('bookings', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_proof_notes');
            }
            if (!Schema::hasColumn('bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_proof_path');
            }
            if (!Schema::hasColumn('bookings', 'overtime_payment_status')) {
                $table->string('overtime_payment_status', 40)->default('tidak_ada')->after('paid_at');
            }
            if (!Schema::hasColumn('bookings', 'overtime_va_number')) {
                $table->string('overtime_va_number', 50)->nullable()->after('overtime_payment_status');
            }
            if (!Schema::hasColumn('bookings', 'overtime_final_price')) {
                $table->decimal('overtime_final_price', 12, 2)->nullable()->after('overtime_va_number');
            }
            if (!Schema::hasColumn('bookings', 'is_payment_forfeited')) {
                $table->boolean('is_payment_forfeited')->default(false)->after('overtime_final_price');
            }
            if (!Schema::hasColumn('bookings', 'forfeited_amount')) {
                $table->decimal('forfeited_amount', 12, 2)->nullable()->after('is_payment_forfeited');
            }
            if (!Schema::hasColumn('bookings', 'forfeited_reason')) {
                $table->string('forfeited_reason')->nullable()->after('forfeited_amount');
            }
            if (!Schema::hasColumn('bookings', 'rescheduled_at')) {
                $table->timestamp('rescheduled_at')->nullable()->after('forfeited_reason');
            }
            if (!Schema::hasColumn('bookings', 'gcal_sync_status')) {
                $table->string('gcal_sync_status', 30)->default('not_applicable')->after('rescheduled_at');
            }
            if (!Schema::hasColumn('bookings', 'last_gcal_synced_at')) {
                $table->timestamp('last_gcal_synced_at')->nullable()->after('gcal_sync_status');
            }
        });

        if (!Schema::hasTable('booking_service_items')) {
            Schema::create('booking_service_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->string('service_tier', 60);
                $table->string('service_package_key', 100);
                $table->string('service_package_name');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('subtotal_price', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('booking_assignees')) {
            Schema::create('booking_assignees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->string('role_type', 30); // psikolog | tester | skorer_manual | skorer_software | editor
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
                $table->timestamp('batch_completed_at')->nullable();
                $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('booking_candidates')) {
            Schema::create('booking_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('client_form_id')->nullable()->constrained('client_forms')->nullOnDelete();
                $table->foreignId('booking_service_item_id')->nullable()->constrained('booking_service_items')->nullOnDelete();
                $table->string('candidate_name');
                $table->string('target_position')->nullable();
                $table->string('job_level')->nullable();
                $table->boolean('is_kilat')->default(false);
                $table->string('attendance_status', 20)->default('pending'); // pending | hadir | absen
                $table->foreignId('assigned_psikolog_id')->nullable()->constrained('counselors')->nullOnDelete();
                $table->foreignId('assigned_editor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('psychologist_deadline_date')->nullable();
                $table->date('client_deadline_date')->nullable();

                $table->timestamp('step_psikotes_at')->nullable();
                $table->foreignId('step_psikotes_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_skoring_at')->nullable();
                $table->foreignId('step_skoring_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_surat_keluar_at')->nullable();
                $table->foreignId('step_surat_keluar_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_draft_hpp_at')->nullable();
                $table->foreignId('step_draft_hpp_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_editing_at')->nullable();
                $table->foreignId('step_editing_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_cetak_at')->nullable();
                $table->foreignId('step_cetak_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_approval_at')->nullable();
                $table->foreignId('step_approval_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('step_kirim_at')->nullable();
                $table->foreignId('step_kirim_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamp('kilat_sent_at')->nullable();
                $table->foreignId('kilat_sent_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('resi_date')->nullable();
                $table->string('resi_number', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('google_calendar_events')) {
            Schema::create('google_calendar_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('google_event_id')->nullable();
                $table->string('google_calendar_id')->nullable();
                $table->string('sync_status', 30)->default('pending_auth'); // synced | pending_auth | failed
                $table->text('last_error')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['booking_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_events');
        Schema::dropIfExists('booking_candidates');
        Schema::dropIfExists('booking_assignees');
        Schema::dropIfExists('booking_service_items');

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
            $cols = array_values(array_filter([
                'pic_name',
                'billing_scheme',
                'requires_outgoing_letter',
                'delivery_mode',
                'final_price',
                'finance_invoice_number',
                'payment_proof_notes',
                'payment_proof_path',
                'paid_at',
                'overtime_payment_status',
                'overtime_va_number',
                'overtime_final_price',
                'is_payment_forfeited',
                'forfeited_amount',
                'forfeited_reason',
                'rescheduled_at',
                'gcal_sync_status',
                'last_gcal_synced_at',
            ], fn ($c) => Schema::hasColumn('bookings', $c)));

            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
