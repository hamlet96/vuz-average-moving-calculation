<?php

namespace App\Actions;

use App\Classes\GoogleSheetsClient;
use Exception;

class CalculateAverageMovingAction
{
    private const DATE_COLUMN_NAME           = 'Date';
    private const VISITOR_COLUMN_NAME        = 'Visitors';
    private const AVERAGE_MOVING_COLUMN_NAME = 'Average Moving';

    protected array $header;

    public function __construct(
        protected readonly GoogleSheetsClient $googleSheetsClient
    ) {
    }

    /**
     * @param string $spreadsheetId
     * @param string $sheetName
     * @return void
     * @throws Exception
     */
    public function run(
        string $spreadsheetId,
        string $sheetName,
    ): void {
        $this->googleSheetsClient->setSpreadsheetId($spreadsheetId);
        $this->validateSpreadsheet();

        $values = $this->getValueRanges($sheetName);

        $this->header = $values[0];

        $this->getColumnIndexWithValidation(self::DATE_COLUMN_NAME);
        $visitorColumnIndex = $this->getColumnIndexWithValidation(self::VISITOR_COLUMN_NAME);
        $averageMovingColumnIndex = $this->getColumnIndex(self::AVERAGE_MOVING_COLUMN_NAME);

        if ($averageMovingColumnIndex === false) {
            // create a new column
            $averageMovingColumnIndex = count($this->header);
            $this->createMovingColumnRange($averageMovingColumnIndex, $sheetName);
            $this->header[] = self::AVERAGE_MOVING_COLUMN_NAME;
        }

        $averageMovingValues = $this->getCalculatedAverageMovingValues($values, $visitorColumnIndex);

        $averageMovingColumnRange = $this->getRangeByIndex($averageMovingColumnIndex);
        $valuesCount = count($values);
        $averageMovingColumnsRange = "{$sheetName}!{$averageMovingColumnRange}2:{$averageMovingColumnRange}{$valuesCount}";

        $this->googleSheetsClient->updateColumns($averageMovingColumnsRange, $averageMovingValues);
    }

    /**
     * Get the index of the column by its name in the header
     * @param string $columnName
     * @return int|string|false
     */
    private function getColumnIndex(string $columnName): int|string|false
    {
        return array_search($columnName, $this->header);
    }

    /**
     * Get the index of the column by its name in the header and throw an exception if it is not found
     *
     * @param string $columnName
     * @return int|string
     * @throws Exception
     */
    private function getColumnIndexWithValidation(string $columnName): int|string
    {
        $columnIndex = $this->getColumnIndex($columnName);
        if ($columnIndex === false) {
            throw new Exception("{$columnName} column not found!");
        }
        return $columnIndex;
    }

    /**
     * Get the values from the spreadsheet as array
     *
     * @param string $sheetName
     * @return array[]
     * @throws Exception
     */
    private function getValueRanges(string $sheetName): array
    {
        try {
            $valueRange = $this->googleSheetsClient->getSpreadsheetValues($sheetName);
        } catch (Exception $e) {
            throw new Exception('Something went wrong when trying to get the Spreadsheet values!', 0, $e);
        }

        $values = $valueRange->getValues();

        if (count($values) < 3) {
            throw new Exception('No necessary data found!');
        }

        return $values;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function validateSpreadsheet(): void
    {
        try {
            $this->googleSheetsClient->getSpreadsheet();
        } catch (Exception $e) {
            throw new Exception('Something went wrong when trying to get the Spreadsheet!', 0, $e);
        }
    }

    /**
     * Create a new column in the spreadsheet
     * @param int $averageMovingColumnIndex
     * @param string $sheetName
     * @return string
     * @throws Exception
     */
    private function createMovingColumnRange(int $averageMovingColumnIndex, string $sheetName): string
    {
        $newColumnRange = $this->getRangeByIndex($averageMovingColumnIndex);
        $averageMovingColumnRange = "{$sheetName}!{$newColumnRange}1";

        try {
            $this->googleSheetsClient->updateColumn($averageMovingColumnRange, self::AVERAGE_MOVING_COLUMN_NAME);
        } catch (Exception $e) {
            throw new Exception('Something went wrong when trying to add a new column!', 0, $e);
        }

        return $averageMovingColumnRange;
    }

    /**
     * Get Google Sheets Range (e.g. A, B, AA, BE, etc.) by index in array
     *
     * @param int $index index in array (starts from 0)
     * @return string
     */
    private function getRangeByIndex(int $index): string
    {
        $index++; //incrementing, because in Google Sheets indexes are starting from 1

        $column = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $column = chr(65 + $remainder) . $column;
            $index = intdiv($index - $remainder, 26);
        }
        return $column;
    }

    /**
     * @param array $values
     * @param int|string $visitorColumnIndex
     * @return array
     */
    private function getCalculatedAverageMovingValues(array $values, int|string $visitorColumnIndex): array
    {
        $averageMovingValues = [];

        for ($i = 1; $i < count($values); $i++) {
            $visitors = (int)$values[$i][$visitorColumnIndex];
            $previousVisitors = (int)$values[$i - 1][$visitorColumnIndex];

            $averageMovingValues[][] = $visitors - $previousVisitors;
        }

        return $averageMovingValues;
    }

}
