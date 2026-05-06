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
        Schema::table('orders', function (Blueprint $table) {
            // PDV sales may not have a registered customer
            $table->foreignUuid('user_id')->nullable()->change();
            // PDV sales are in-store; no shipping address needed
            $table->json('shipping_address')->nullable()->change();

            $table->enum('source', ['online', 'pdv'])->default('online')->after('id');
            $table->foreignUuid('cash_session_id')->nullable()->after('source')
                ->constrained('cash_sessions')->nullOnDelete();
            $table->string('pdv_customer_name')->nullable()->after('notes');
            $table->string('pdv_customer_doc')->nullable()->after('pdv_customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropColumn(['source', 'cash_session_id', 'pdv_customer_name', 'pdv_customer_doc']);
            $table->foreignUuid('user_id')->nullable(false)->change();
            $table->json('shipping_address')->nullable(false)->change();
        });
    }
};
