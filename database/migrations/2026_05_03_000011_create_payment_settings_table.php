<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('gateway', ['mercadopago', 'asaas'])->unique();
            // Credentials stored as AES-256-CBC encrypted JSON via Crypt::encrypt()
            $table->text('credentials');
            $table->boolean('active')->default(false);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
