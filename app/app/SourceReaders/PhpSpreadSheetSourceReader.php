<?php

declare(strict_types=1);

namespace App\SourceReaders;

use App\UnsupportedFeatureException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PhpSpreadSheetSourceReader extends AbstractSourceReader implements ISourceReader, ICanUseFilePathSourceReader
{
    protected ?Spreadsheet $spreadsheet;

    protected array $columns;

    protected int $currentRowIndex;

    protected int $highestRowIndex;

    public function __construct(protected ?string $filePath = null, ?string $fileType = null)
    {
        $this->setFileType($fileType);
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function load(): void
    {
        if (!$this->fileType) {
            $this->spreadsheet = IOFactory::load($this->filePath, IReader::READ_DATA_ONLY);
        } else {
            $reader = match ($this->fileType) {
                'xlsx' => new Xlsx(),
                'xls' => new Xls(),
                default => throw new UnsupportedFeatureException("Unsupported file type \"{$this->fileType}\""),
            };
            $reader->setReadDataOnly(true);
            $this->spreadsheet = $reader->load($this->filePath);
        }
        $this->readColumnNames();
        $this->prepareForChunkReading();
    }

    public function close(): void
    {
        $this->spreadsheet = null;
    }

    public function prepareForChunkReading(): void
    {
        $this->currentRowIndex = 2;
        $this->highestRowIndex = $this->spreadsheet->getActiveSheet()->getHighestRow();
    }

    public function chunkNextRows(): array
    {
        if ($this->currentRowIndex > $this->highestRowIndex) {
            return [];
        }

        $endRowIndex = $this->currentRowIndex + $this->chunk - 1;
        if ($endRowIndex > $this->highestRowIndex) {
            $endRowIndex = $this->highestRowIndex;
        }

        $rows = [];
        foreach ($this->spreadsheet->getActiveSheet()->getRowIterator($this->currentRowIndex, $endRowIndex) as $row) {
            $rowData = [];

            foreach ($row->getColumnIterator() as $column) {
                $columnName = $this->columns[$column->getColumn()];
                $rowData[$columnName] = $this->prepareValue($column->getValue());
            }

            $rows[] = $rowData;
        }
        $this->currentRowIndex = $endRowIndex + 1;

        return $rows;
    }

    protected function prepareValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        } elseif (validate_int($value)) {
            $value = (int)$value;
        } elseif (validate_float($value)) {
            $value = (float)$value;
        } elseif (is_null($value) || is_bool($value)) {
            $value = '';
        }

        return $value;
    }

    protected function readColumnNames(): void
    {
        $this->spreadsheet->getActiveSheet()->getRowIterator()->resetStart();
        $row = $this->spreadsheet->getActiveSheet()->getRowIterator()->current();
        foreach ($row->getColumnIterator() as $column) {
            $this->columns[$column->getColumn()] = $this->prepareValue($column->getValue());
        }
    }

    public function getColumnNames(): array
    {
        return $this->columns;
    }
}
