<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('assign_role', 20)->nullable()->after('description');
        });

        DB::table('tags')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%канон%'])
                    ->orWhere('slug', 'like', '%kanon%');
            })
            ->update(['assign_role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('assign_role');
        });
    }
};
