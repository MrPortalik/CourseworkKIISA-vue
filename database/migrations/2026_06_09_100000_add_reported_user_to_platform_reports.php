<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_reports', function (Blueprint $table) {
            $table->foreignId('reported_user_id')->nullable()->after('article_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reported_user_id');
        });
    }
};
