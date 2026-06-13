<?php

namespace App\Imports;

use App\Models\Ticker;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TickersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // The headings are automatically slugified by default (e.g., 'Kode Saham' -> 'kode_saham')
            if (!isset($row['kode_saham']) || !isset($row['nama_perusahaan'])) {
                continue;
            }

            $currentPrice = isset($row['penutupan']) && is_numeric($row['penutupan']) 
                ? (float) $row['penutupan'] 
                : null;

            Ticker::updateOrCreate(
                ['symbol' => trim($row['kode_saham'])],
                [
                    'company_name' => trim($row['nama_perusahaan']),
                    'current_price' => $currentPrice,
                ]
            );
        }
    }
}
