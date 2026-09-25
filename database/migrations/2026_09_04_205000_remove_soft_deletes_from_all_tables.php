<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Purge currently soft-deleted data so they don't reappear as active
        if (Schema::hasColumn('counselors', 'deleted_at')) {
            DB::table('counselors')->whereNotNull('deleted_at')->delete();
        }
        if (Schema::hasColumn('bookings', 'deleted_at')) {
            DB::table('bookings')->whereNotNull('deleted_at')->delete();
        }
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'deleted_at')) {
            DB::table('clients')->whereNotNull('deleted_at')->delete();
        }

        // 2. Drop deleted_at columns
        Schema::table('counselors', function (Blueprint $table) {
            if (Schema::hasColumn('counselors', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (Schema::hasColumn('clients', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselors', function (Blueprint $table) {
            $table->softDeletes();
        });

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
