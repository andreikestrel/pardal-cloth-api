<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single-row table — one settings record per application instance
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name');
            $table->string('primary_color', 7)->default('#000000');
            $table->string('secondary_color', 7)->default('#000000');
            $table->string('accent_color', 7)->default('#000000');
            // Stored via Spatie Media Library on the Settings model — path kept here as fallback
            $table->string('logo')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
