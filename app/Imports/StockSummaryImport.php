<?php

namespace App\Imports;

use App\Models\StockPrice;
use App\Models\EodUpload;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StockSummaryImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue, WithEvents
{
    public function __construct(public EodUpload $upload)
    {
    }

    public function model(array $row)
    {
        // Increment processed rows count
        $this->upload->increment('processed_rows');

        // Check required fields
        if (empty($row['stock_code']) || empty($row['last_trading_date'])) {
            return null;
        }

        try {
            $tradingDate = is_numeric($row['last_trading_date']) 
                ? Carbon::instance(Date::excelToDateTimeObject($row['last_trading_date']))
                : Carbon::parse($row['last_trading_date']);
        } catch (\Exception $e) {
            $tradingDate = $this->upload->trading_date;
        }

        return StockPrice::updateOrCreate(
            [
                'kode' => $row['stock_code'],
                'date' => $tradingDate->format('Y-m-d'),
            ],
            [
                'open' => $row['open_price'] ?? 0,
                'high' => $row['high'] ?? 0,
                'low' => $row['low'] ?? 0,
                'close' => $row['close'] ?? 0,
                'last_price' => $row['close'] ?? 0,
                'volume' => $row['volume'] ?? 0,
                'value' => $row['value'] ?? 0,
                'frequency' => $row['frequency'] ?? 0,
                'source' => 'excel_eod',
                'price_type' => 'close',
            ]
        );
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                $this->upload->update(['status' => 'completed']);
            },
            ImportFailed::class => function(ImportFailed $event) {
                $this->upload->update([
                    'status' => 'failed',
                    'error_message' => $event->getException()->getMessage()
                ]);
            },
        ];
    }
}
