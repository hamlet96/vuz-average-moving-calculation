<?php

namespace Tests\Unit;

use App\Actions\CalculateAverageMovingAction;
use App\Classes\GoogleSheetsClient;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Tests\TestCase;

class CalculateAverageMovingActionTest extends TestCase
{
    /**
     * @dataProvider averageMovingDataProvider
     */
    public function test_should_set_average_moving($data, $averageMovingData): void
    {
        // Mock GoogleSheetsClient
        $googleSheetsClient = $this->createMock(GoogleSheetsClient::class);
        $googleSheetsClient
            ->expects($this->once())
            ->method('getSpreadsheet')
            ->willReturn(new Spreadsheet());

        $valueRange = new ValueRange();
        $valueRange->setValues($data);

        $googleSheetsClient
            ->expects($this->once())
            ->method('getSpreadsheetValues')
            ->with($this->equalTo('testSheet'))
            ->willReturn($valueRange);

        // Set up the expectations for the updateColumns method
        $googleSheetsClient
            ->expects($this->once())
            ->method('updateColumns')
            ->with(
                $this->equalTo('testSheet!C2:C4'),
                $this->equalTo($averageMovingData)
            );

        $action = new CalculateAverageMovingAction($googleSheetsClient);

        // Run the action
        $action->run(spreadsheetId: 'testSpreadsheetId', sheetName: 'testSheet');
    }


    /**
     * @dataProvider dataWhichShouldCauseException
     */
    public function test_should_throw_exception(array $data): void
    {
        // Mock GoogleSheetsClient
        $googleSheetsClient = $this->createMock(GoogleSheetsClient::class);
        $googleSheetsClient
            ->expects($this->once())
            ->method('getSpreadsheet')
            ->willReturn(new Spreadsheet());

        $valueRange = new ValueRange();
        $valueRange->setValues($data);

        $googleSheetsClient
            ->expects($this->once())
            ->method('getSpreadsheetValues')
            ->with($this->equalTo('testSheet'))
            ->willReturn($valueRange);


        $action = new CalculateAverageMovingAction($googleSheetsClient);

        $this->expectException(\Exception::class);
        // Run the action
        $action->run(spreadsheetId: 'testSpreadsheetId', sheetName: 'testSheet');
    }

    public function averageMovingDataProvider(): array
    {
        return [
            'without Average Moving' => [
                [
                    ['Date', 'Visitors'],
                    ['2023-01-01', 10],
                    ['2023-01-02', 15],
                    ['2023-01-03', 20],
                ],
                [
                    [10],   // 10
                    [5],    // 15 - 10
                    [5],    // 20 - 15
                ]
            ],
            'with Average Moving Header'    => [
                [
                    ['Date', 'Visitors', 'Average Moving'],
                    ['2023-01-01', 10],
                    ['2023-01-02', 15],
                    ['2023-01-03', 20],
                ],
                [
                    [10],   // 10
                    [5],    // 15 - 10
                    [5],    // 20 - 15
                ]
            ],
            'with Average Moving Header and Wrong Data'    => [
                [
                    ['Date', 'Visitors', 'Average Moving'],
                    ['2023-01-01', 10, 3],
                    ['2023-01-02', 15, 3],
                    ['2023-01-03', 20, 3],
                ],
                [
                    [10],   // 10
                    [5],    // 15 - 10
                    [5],    // 20 - 15
                ]
            ],

        ];
    }

    public function dataWhichShouldCauseException(): array
    {
        return [
            'without Date header' => [
                [
                    ['WrongDate', 'Visitors'],
                ],
            ],
            'without Visitors header' => [
                [
                    ['Date', 'WrongVisitors'],
                ],
            ],
            'only Header' => [
                [
                    ['Date', 'Visitors'],
                ],
            ],
            'Header and 1 Row' => [
                [
                    ['Date', 'Visitors'],
                    ['2023-01-01', 10],
                ],
            ],
        ];
    }

}
