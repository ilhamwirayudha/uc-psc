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
        // 1. Drop foreign keys on tables referencing patients
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        Schema::table('pairings', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        Schema::table('patient_staff_assignments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        // 2. Rename tables
        Schema::rename('patients', 'clients');
        Schema::rename('patient_staff_assignments', 'client_staff_assignments');

        // 3. Rename columns and re-add foreign key constraints to clients(id)
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'client_id');
        });
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('pairings', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'client_id');
        });
        Schema::table('pairings', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'client_id');
        });
        Schema::table('test_results', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('client_staff_assignments', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'client_id');
        });
        Schema::table('client_staff_assignments', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'client_id');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys on tables referencing clients
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('pairings', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('client_staff_assignments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        // 2. Rename tables back
        Schema::rename('clients', 'patients');
        Schema::rename('client_staff_assignments', 'patient_staff_assignments');

        // 3. Rename columns back and re-add foreign key constraints to patients(id)
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->renameColumn('client_id', 'patient_id');
        });
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('pairings', function (Blueprint $table) {
            $table->renameColumn('client_id', 'patient_id');
        });
        Schema::table('pairings', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->renameColumn('client_id', 'patient_id');
        });
        Schema::table('test_results', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('patient_staff_assignments', function (Blueprint $table) {
            $table->renameColumn('client_id', 'patient_id');
        });
        Schema::table('patient_staff_assignments', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('client_id', 'patient_id');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });
    }
};
