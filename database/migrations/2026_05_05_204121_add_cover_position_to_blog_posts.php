<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // CSS object-position value applied to the cover image — lets the author
            // pick the focal point (e.g. "50% 80%" to keep the bottom of the photo visible)
            $table->string('cover_position', 20)->default('50% 50%')->after('body_html');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('cover_position');
        });
    }
};
