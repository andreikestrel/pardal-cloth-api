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
        // Extend enums to support PDV manual payments
        DB::statement("ALTER TABLE payments MODIFY COLUMN gateway ENUM('mercadopago','asaas','manual') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('pix','boleto','credit_card','checkout_pro','cash') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('pix','boleto','credit_card','checkout_pro') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY COLUMN gateway ENUM('mercadopago','asaas') NOT NULL");
    }
};
