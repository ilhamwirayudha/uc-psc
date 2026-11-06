<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('jenis', 50)->default('individual')->after('name');
            $table->foreignId('assigned_staff_id')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['assigned_staff_id']);
            $table->dropColumn(['jenis', 'assigned_staff_id']);
        });
    }
};
