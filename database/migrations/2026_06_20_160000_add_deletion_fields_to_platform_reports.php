<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_reports', function (Blueprint $table) {
            $table->text('deletion_reason')->nullable()->after('responded_at');
            $table->foreignId('deleted_by_id')->nullable()->after('deletion_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->after('deleted_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('platform_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by_id');
            $table->dropColumn(['deletion_reason', 'deleted_at']);
        });
    }
};
