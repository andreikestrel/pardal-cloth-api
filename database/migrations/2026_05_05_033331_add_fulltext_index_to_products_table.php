<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // FULLTEXT supports MATCH ... AGAINST for fast natural-language and boolean-mode search
            $table->fullText(['name', 'description'], 'products_search_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText('products_search_idx');
        });
    }
};
