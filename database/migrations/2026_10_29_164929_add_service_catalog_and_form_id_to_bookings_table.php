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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('client_form_id')->nullable()->after('client_id')->constrained('client_forms')->nullOnDelete();
            $table->string('kategori', 50)->nullable()->change();
            $table->string('service_tier', 50)->nullable()->after('kategori');
            $table->string('service_package_key', 100)->nullable()->after('service_tier');
            $table->string('service_package_name', 255)->nullable()->after('service_package_key');
            $table->decimal('price', 12, 2)->nullable()->after('service_package_name');
            $table->decimal('overtime_price', 12, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['client_form_id']);
            $table->dropColumn([
                'client_form_id',
                'service_tier',
                'service_package_key',
                'service_package_name',
                'price',
                'overtime_price',
            ]);
        });
    }
};
