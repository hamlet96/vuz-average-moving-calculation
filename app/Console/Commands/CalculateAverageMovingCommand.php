<?php

namespace App\Console\Commands;

use App\Actions\CalculateAverageMovingAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateAverageMovingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-average-moving {--spreadsheetId=} {--sheetName=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate average moving for the spreadsheet';

    /**
     * Execute the console command.
     */
    public function handle(
        CalculateAverageMovingAction $visitorService
    ) {
        if ($this->option('spreadsheetId')) {
            $spreadsheetId = $this->option('spreadsheetId');
        } else {
            $spreadsheetId = config('average-moving.spreadsheet_id');
        }

        if ($this->option('sheetName')) {
            $sheetName = $this->option('sheetName');
        } else {
            $sheetName = config('average-moving.sheet_name');
        }

        try {
            $visitorService->run($spreadsheetId, $sheetName);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            $this->error($e->getMessage());
            return;
        }

        $this->info('Done!');
    }
}
