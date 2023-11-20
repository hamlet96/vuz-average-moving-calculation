<?php

namespace App\Classes;

use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class GoogleSheetsClient
{
    protected Google_Service_Sheets $service;
    protected string                $spreadsheetId;

    public function __construct(
        Google_Client $client
    )
    {
        $client->setApplicationName('Google Sheets API');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');

        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        $path = config('average-moving.credentials_path');
        $client->setAuthConfig($path);

        $this->service = new Google_Service_Sheets($client);
    }

    public function setSpreadsheetId(string $spreadsheetId)
    {
        $this->spreadsheetId = $spreadsheetId;
    }

    /**
     * @return Spreadsheet
     * @throws \Exception
     */
    public function getSpreadsheet(): Spreadsheet
    {
        return $this->service->spreadsheets->get($this->spreadsheetId);
    }

    /**
     * @param string $range
     * @return ValueRange
     */
    public function getSpreadsheetValues(string $range): ValueRange
    {
        return $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
    }

    public function updateColumn(string $range, $value)
    {
        $this->updateColumns($range, [[$value]]);
    }

    public function updateColumns(string $range, array $values)
    {
        $valueRange = new Google_Service_Sheets_ValueRange(
            [
                'values' => $values
            ]
        );
        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $valueRange, $params);
    }


}
