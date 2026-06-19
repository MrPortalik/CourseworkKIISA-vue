<?php

namespace App\Console\Commands;

use App\Support\UserEmailHash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportUserEmailsCommand extends Command
{
    protected $signature = 'users:export-emails
                            {--path= : Путь к JSON-файлу (по умолчанию storage/app/user-emails-backup.json)}';

    protected $description = 'Выгрузить plain-text email пользователей в JSON (резервная копия перед хэшированием)';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/user-emails-backup.json');
        $rows = $this->fetchUserRows();
        $export = [];

        foreach ($rows as $row) {
            $plain = UserEmailHash::resolvePlain($row->email, $row->email_hash ?? null);

            $export[] = [
                'id' => (int) $row->id,
                'email' => $plain,
                'already_hashed' => $plain === null && UserEmailHash::isHashed($row->email),
            ];
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $withPlain = count(array_filter($export, fn ($item) => $item['email'] !== null));
        $this->info("Экспортировано записей: ".count($export)." (plain-text: {$withPlain})");
        $this->line("Файл: {$path}");

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function fetchUserRows()
    {
        $query = DB::table('users')->select('id', 'email')->orderBy('id');

        if (Schema::hasColumn('users', 'email_hash')) {
            $query->addSelect('email_hash');
        }

        return $query->get();
    }
}
