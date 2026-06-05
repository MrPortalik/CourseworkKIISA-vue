<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('publication_rejection_reason')->nullable()->after('is_publishable');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('publication_rejection_reason');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
