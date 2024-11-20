<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ImportSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-sql
                            {filepath=dump.sql : Путь до SQL файла от папки storage/}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = storage_path($this->argument('filepath'));

        if (!file_exists($filePath)) {
            $this->error('Файл "' . $filePath . '" не найден');
            return 1;
        }

        $sql = file_get_contents($filePath);

        if (!$sql) {
            $this->error('Пустой SQL-файл "' . $filePath . '"');
            return 1;
        }

        DB::unprepared($sql);

        $this->info('Файл "' . $filePath . '" успешно импортирован');
        return 0;
    }
}
