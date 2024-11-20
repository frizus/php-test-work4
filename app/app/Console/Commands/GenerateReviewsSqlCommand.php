<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\SourceReaders\SourceReaderBuilder;
use App\SqlGenerator\FileOutput;
use App\SqlGenerator\QueryFormer;
use App\SqlGenerator\ReviewsSqlGenerator;
use App\UnsupportedFeatureException;
use Illuminate\Console\Command;

final class GenerateReviewsSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reviews-sql
                            {filepath=reviews.xlsx : Путь до xlsx файла от папки storage/}
                            {output-path? : Куда сохранять sql-файл от папки storage/ (по умолчанию, добавит к filepath расширение .sql)}
                            {--format : Указать конкретно формат файла (xlsx, xls). Если не указывать определяется по расширению}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     * @throws UnsupportedFeatureException
     */
    public function handle(): int
    {
        $format = (string)$this->option('format');
        $rawFilePath = $this->argument('filepath');
        $filePath = storage_path($rawFilePath);
        $relativeOutputPath = $this->argument('output-path') ?: $rawFilePath . '.sql';
        $fileOutput = new FileOutput($relativeOutputPath);

        $importer = new ReviewsSqlGenerator(
            (new SourceReaderBuilder($format))
                ->setFilePath($filePath)
                ->build(),
            new QueryFormer(),
            $fileOutput
        );
        $importer->run();

        foreach ($importer->getImportStats() as $name => $value) {
            $this->info($name . ': ' . $value);
        }

        $this->info('Сохранено в "' . $fileOutput->outputPath() . '"');

        return 0;
    }
}
