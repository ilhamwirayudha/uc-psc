<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'counselor_id')) {
                $table->foreignId('counselor_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('counselors')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'google_email')) {
                $table->string('google_email')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'google_access_token')) {
                $table->text('google_access_token')->nullable()->after('google_email');
            }
            if (!Schema::hasColumn('users', 'google_refresh_token')) {
                $table->text('google_refresh_token')->nullable()->after('google_access_token');
            }
            if (!Schema::hasColumn('users', 'google_token_expires_at')) {
                $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
            }
            if (!Schema::hasColumn('users', 'google_calendar_id')) {
                $table->string('google_calendar_id')->nullable()->after('google_token_expires_at');
            }
            if (!Schema::hasColumn('users', 'google_connected_at')) {
                $table->timestamp('google_connected_at')->nullable()->after('google_calendar_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'counselor_id')) {
                $table->dropConstrainedForeignId('counselor_id');
            }
            $dropCols = array_values(array_filter([
                'google_email',
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
                'google_calendar_id',
                'google_connected_at',
            ], fn ($col) => Schema::hasColumn('users', $col)));

            if ($dropCols !== []) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
