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
        Schema::dropIfExists('test_result_activities');
        Schema::dropIfExists('test_result_documents');
        Schema::dropIfExists('test_results');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables removed permanently as part of removing Test Results module
    }
};
