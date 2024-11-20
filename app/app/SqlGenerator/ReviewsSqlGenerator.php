<?php

declare(strict_types=1);

namespace App\SqlGenerator;

use App\SourceReaders\ISourceReader;
use Illuminate\Support\Arr;

class ReviewsSqlGenerator extends AbstractSqlGenerator
{
    protected const array MAP = [
        'product_id' => 'UF_BITRIX_ITEM_ID',
        'comment_id' => 'UF_MARKET_ID',
        'author' => 'UF_NAME',
        'created_at' => 'UF_DATETIME',
        'likes' => 'UF_AGREE',
        'unlikes' => 'UF_DISAGREE',
        'photo' => 'UF_MEDIA',
        'rating' => 'UF_GRADE',
        'text' => 'UF_TEXT',
    ];

    protected const string IDENTIFIER_FIELD = 'comment_id';

    protected const array FIELD_NORMALIZERS = [
        'comment_id' => 'ozonMarketId',
        'author' => 'author',
        'created_at' => 'russianDatetime',
        'photo' => 'media',
        'text' => 'string',
    ];

    protected const array EXTRA_SQL_FIELDS_WITH_DEFAULT_VALUES = [
        'UF_ACTIVE' => 1,
        'UF_USER_ID' => null,
        'UF_PHOTOS' => '',
        'UF_EMAIL' => '',
        'UF_SITE_ID' => self::SITE_ID,
        'UF_SOURCE' => self::SOURCE,
    ];

    public const string SITE_ID = '0f';

    public const int SOURCE = 15;

    public const string TABLE_NAME = 'app_product_review';

    public const int BULK_INSERT_LIMIT = 50;

    protected array $batch;

    protected array $insertColumnNames;

    public function __construct(
        protected ISourceReader $sourceReader,
        protected IQueryFormer $queryFormer,
        protected IOutput $output
    ) {
    }

    public function run(): void
    {
        $this->sourceReader->load();
        $this->output->prepare();
        $this->initStats();
        $this->batch = [];
        while ($rows = $this->sourceReader->chunkNextRows()) {
            $this->incrStat('total', count($rows));
            $this->removeInvalidRows($rows);
            $this->removeUnneededColumns($rows);
            $this->normalizeRowsValues($rows);
            $this->rearrangeForBatchAndAddExtraColumns($rows);
            $this->addToBatch($rows);
            $this->generateBatch();
        }

        $this->generateBatch(true);

        $this->sourceReader->close();
        $this->output->finish();
    }

    protected function generateBatch(bool $last = false): void
    {
        $newBatch = null;

        foreach (array_chunk($this->batch, self::BULK_INSERT_LIMIT) as $rows) {
            if (
                !$last
                && (count($rows) < self::BULK_INSERT_LIMIT)
            ) {
                $newBatch = $rows;
                break;
            }

            $sql = $this->queryFormer->formBatch(
                self::TABLE_NAME,
                $this->getInsertColumnNames(),
                $rows
            );

            $this->output->write($sql);

            $this->incrStat('add', count($rows));
            $this->incrStat('multiple_insert_queries_count');
        }

        if ($newBatch) {
            $this->batch = $newBatch;
        }
    }

    protected function addToBatch(array $rows): void
    {
        if (!$rows) {
            return;
        }

        $this->batch = [
            ...$this->batch,
            ...$rows
        ];
    }

    protected function removeUnneededColumns(array &$rows): void
    {
        $columns = $this->selectedColumns();

        foreach ($rows as &$row) {
            $row = Arr::only($row, $columns);
        }
    }

    protected function rearrangeForBatchAndAddExtraColumns(array &$rows): void
    {
        foreach ($rows as &$row) {
            $newRow = array_fill_keys(
                $this->getInsertColumnNames(),
                null
            );

            foreach ($row as $fieldName => $value) {
                if (!key_exists($fieldName, self::MAP)) {
                    continue;
                }

                $mappedName = self::MAP[$fieldName];
                $newRow[$mappedName] = $value;
            }

            foreach (self::EXTRA_SQL_FIELDS_WITH_DEFAULT_VALUES as $key => $value) {
                $newRow[$key] = $value;
            }

            $row = $newRow;
        }
    }

    protected function removeInvalidRows(array &$rows): void
    {
        foreach ($rows as $key => $row) {
            if ($this->isRepeatedHeaderRow($row)) {
                $this->incrStat('error_row_is_repeated_header');
                unset($rows[$key]);
            } elseif ($this->isEmptyRow($row)) {
                $this->incrStat('error_row_is_empty');
                unset($rows[$key]);
            } elseif (!$row[self::IDENTIFIER_FIELD]) {
                $this->incrStat('error_row_without_identifier');
                unset($rows[$key]);
            }
        }
    }

    protected function getInsertColumnNames(): array
    {
        if (!isset($this->insertColumnNames)) {
            $this->insertColumnNames = array_merge(
                array_values(self::MAP),
                array_keys(self::EXTRA_SQL_FIELDS_WITH_DEFAULT_VALUES)
            );
        }

        return $this->insertColumnNames;
    }

    protected function selectedColumns(): array
    {
        return array_keys(self::MAP);
    }

    protected function isRepeatedHeaderRow(array $rowData): bool
    {
        return array_keys($rowData) === array_values($rowData);
    }

    protected function isEmptyRow(array $rowData): bool
    {
        foreach ($rowData as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return (bool)$rowData;
    }

    protected function incrStat($name, $incrBy = 1): void
    {
        parent::incrStat($name, $incrBy);

        if (in_array(
            $name,
            [
                'error_row_is_repeated_header',
                'error_row_is_empty',
                'error_row_without_identifier'
            ],
            true
        )) {
            parent::incrStat('broken');
        }
    }

    protected function statsKeys(): array
    {
        return [
            'total',
            'add',
            'broken',
            'error_row_is_repeated_header',
            'error_row_is_empty',
            'error_row_without_identifier',
            'multiple_insert_queries_count',
        ];
    }
}
