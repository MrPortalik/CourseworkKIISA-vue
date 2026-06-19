<?php

namespace App\Console\Commands;

use App\Support\UserEmailHash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HashUserEmailsCommand extends Command
{
    protected $signature = 'users:migrate-emails
                            {--skip-export : Не создавать JSON-бэкап}
                            {--path= : Путь для JSON-бэкапа}';

    protected $description = 'Перенести plain-text email в SHA-256 хэш в колонке email (без Eloquent)';

    public function handle(): int
    {
        if (! $this->option('skip-export')) {
            $this->call('users:export-emails', [
                '--path' => $this->option('path'),
            ]);
        }

        $rows = $this->fetchUserRows();
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $targetHash = UserEmailHash::targetHash($row->email, $row->email_hash ?? null);

            if (! $targetHash) {
                $this->warn("Пропуск id={$row->id}: не удалось определить email.");
                $failed++;

                continue;
            }

            if ($row->email === $targetHash && UserEmailHash::isHashed($row->email)) {
                $skipped++;

                continue;
            }

            DB::table('users')->where('id', $row->id)->update(['email' => $targetHash]);
            $updated++;
        }

        $this->finalizeSchema();

        $this->info("Готово. Обновлено: {$updated}, уже хэшировано: {$skipped}, пропущено: {$failed}.");
        $this->line('В колонке users.email теперь только SHA-256 хэши.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
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

    protected function finalizeSchema(): void
    {
        if (Schema::hasColumn('users', 'email_hash')) {
            $this->dropIndexIfExists('users', 'users_email_hash_unique');

            Schema::table('users', function ($table) {
                $table->dropColumn('email_hash');
            });

            $this->line('Колонка email_hash удалена.');
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(64) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN email TYPE VARCHAR(64)');
        }

        $this->dropIndexIfExists('users', 'users_email_unique');

        if (! $this->indexExists('users', 'users_email_unique')) {
            Schema::table('users', function ($table) {
                $table->unique('email');
            });
        }

        $this->line('Схема users.email приведена к VARCHAR(64) + UNIQUE.');
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function ($tableBlueprint) use ($indexName) {
            if ($indexName === 'users_email_unique') {
                $tableBlueprint->dropUnique(['email']);
            } elseif ($indexName === 'users_email_hash_unique') {
                $tableBlueprint->dropUnique(['email_hash']);
            }
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($index) => $index->name === $indexName);
        }

        if ($driver === 'mysql') {
            $database = Schema::getConnection()->getDatabaseName();
            $result = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$database, $table, $indexName],
            );

            return count($result) > 0;
        }

        if ($driver === 'pgsql') {
            $result = DB::select(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
                [$table, $indexName],
            );

            return count($result) > 0;
        }

        return false;
    }
}
