<?php

namespace App\Console\Commands;

use App\Support\UserEmailHash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillEmailEncryptedCommand extends Command
{
    protected $signature = 'users:backfill-email-encrypted
                            {--path= : JSON-бэкап из users:export-emails (по умолчанию storage/app/user-emails-backup.json)}';

    protected $description = 'Заполнить email_encrypted из JSON-бэкапа plain-text адресов';

    public function handle(): int
    {
        if (! Schema::hasColumn('users', 'email_encrypted')) {
            $this->error('Колонка users.email_encrypted отсутствует. Сначала выполните: php artisan migrate');

            return self::FAILURE;
        }

        $path = $this->option('path') ?: storage_path('app/user-emails-backup.json');

        if (! is_file($path)) {
            $this->error("Файл не найден: {$path}");
            $this->line('Создайте бэкап командой: php artisan users:export-emails');

            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($path), true);

        if (! is_array($items)) {
            $this->error('Некорректный JSON в файле бэкапа.');

            return self::FAILURE;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $plain = $item['email'] ?? null;

            if (! $id || ! is_string($plain) || $plain === '') {
                $skipped++;

                continue;
            }

            $row = DB::table('users')->where('id', $id)->first(['id', 'email', 'email_encrypted']);

            if (! $row) {
                $skipped++;

                continue;
            }

            if (! empty($row->email_encrypted)) {
                $skipped++;

                continue;
            }

            if (UserEmailHash::hash($plain) !== $row->email) {
                $this->warn("Пропуск id={$id}: hash не совпадает с бэкапом.");
                $skipped++;

                continue;
            }

            DB::table('users')->where('id', $id)->update([
                'email_encrypted' => encrypt(mb_strtolower(trim($plain))),
            ]);

            $updated++;
        }

        $this->info("Готово. Заполнено: {$updated}, пропущено: {$skipped}.");

        return self::SUCCESS;
    }
}
