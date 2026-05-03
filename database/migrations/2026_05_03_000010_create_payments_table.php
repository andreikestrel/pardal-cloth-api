<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->enum('gateway', ['mercadopago', 'asaas']);
            $table->string('gateway_payment_id');
            $table->enum('method', ['pix', 'boleto', 'credit_card', 'checkout_pro']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->text('pix_code')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('boleto_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
