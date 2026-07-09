<?php

namespace App\Imports;

use App\Models\MasterStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class MasterStockImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row[5]) || trim($row[5]) === '') {
                continue; // Skip if no stock code
            }
            
            $code = trim($row[5]);
            
            MasterStock::updateOrCreate(
                ['code' => $code],
                [
                    'name' => isset($row[6]) ? trim($row[6]) : null,
                    'sector' => isset($row[2]) ? trim($row[2]) : null,
                    'sub_industry' => isset($row[4]) ? trim($row[4]) : null,
                    'is_sharia' => isset($row[7]) && trim($row[7]) === 'S',
                ]
            );
        }
    }

    public function startRow(): int
    {
        return 5;
    }
}
