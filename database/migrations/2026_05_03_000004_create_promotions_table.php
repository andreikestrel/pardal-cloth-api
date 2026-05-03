<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('trigger_type', ['min_qty', 'min_amount', 'product', 'category']);
            $table->enum('discount_type', ['percentage', 'fixed', 'free_item', 'free_shipping']);
            $table->decimal('discount_value', 10, 2);
            $table->integer('min_items')->nullable();
            $table->decimal('min_amount', 10, 2)->nullable();
            // Scoped target — product_id or category_id depending on trigger_type
            $table->uuid('target_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('priority')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
