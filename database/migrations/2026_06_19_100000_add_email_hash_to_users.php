<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Перенос данных email → SHA-256 выполняется командой:
 *   php artisan users:migrate-emails
 *
 * Эта миграция не трогает данные и не использует модель User,
 * чтобы не падать на plain-text / частично зашифрованных значениях.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Схема и данные обрабатываются в users:migrate-emails.
        // Здесь только отметка в migrations, что шаг подготовлен.
    }

    public function down(): void
    {
        // Откат данных — из JSON-бэкапа вручную или через users:export-emails до отката.
    }
};
