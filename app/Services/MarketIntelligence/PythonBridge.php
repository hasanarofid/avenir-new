<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketSnapshot;
use Carbon\Carbon;

/**
 * PythonBridge: Exports DB data to CSV and invokes client's Python scripts.
 * Strategy: Python scripts are the source of truth — no PHP reimplementation.
 */
class PythonBridge
{
    private string $scriptsDir;
    private string $tempDir;

    public function __construct()
    {
        $this->scriptsDir = base_path('scripts/python/avenir_regime_engine_py');
        $this->tempDir    = storage_path('app/python_bridge');
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Export IHSG OHLC history (up to $days days) ending at $date to CSV.
     * Columns: date, open, high, low, close, previous_close, market_value, foreign_net
     */
    public function exportOhlcCsv(string $date, int $days = 300): string
    {
        $rows = MarketSnapshot::whereIn('symbol_or_metric', [
                'IHSG', 'OPEN', 'HIGH', 'LOW', 'FOREIGN_NET_TODAY', 'VALUE_TRADED_BN_IDR'
            ])
            ->whereDate('date', '<=', $date)
            ->orderBy('date', 'desc')
            ->limit($days * 6)
            ->get()
            ->groupBy('date');

        $csv  = "date,open,high,low,close,previous_close,market_value,foreign_net\n";
        $byDate = [];

        foreach ($rows as $d => $snaps) {
            $map = $snaps->keyBy('symbol_or_metric');
            $byDate[$d] = [
                'date'        => substr((string)$d, 0, 10), // YYYY-MM-DD only
                'open'        => (float) ($map->get('OPEN')->value  ?? 0),
                'high'        => (float) ($map->get('HIGH')->value  ?? 0),
                'low'         => (float) ($map->get('LOW')->value   ?? 0),
                'close'       => (float) ($map->get('IHSG')->value  ?? 0),
                'market_value'  => (float) ($map->get('VALUE_TRADED_BN_IDR')->value ?? 0),
                'foreign_net'   => (float) ($map->get('FOREIGN_NET_TODAY')->value   ?? 0),
            ];
        }

        // Sort chronologically and compute previous_close
        ksort($byDate);
        $dateList = array_values($byDate);
        $limit    = count($dateList);

        for ($i = 0; $i < $limit; $i++) {
            $prev = $i > 0 ? $dateList[$i - 1]['close'] : $dateList[$i]['open'];
            $r    = $dateList[$i];
            $csv .= implode(',', [
                $r['date'], $r['open'], $r['high'], $r['low'],
                $r['close'], $prev, $r['market_value'], $r['foreign_net'],
            ]) . "\n";
        }

        $path = $this->tempDir . "/ohlc_{$date}.csv";
        file_put_contents($path, $csv);
        return $path;
    }

    /**
     * Export foreign flow history to separate CSV (for flow.py).
     * Columns: date, foreign_net, market_value
     */
    public function exportFlowCsv(string $date, int $days = 60): string
    {
        $rows = MarketSnapshot::whereIn('symbol_or_metric', ['FOREIGN_NET_TODAY', 'VALUE_TRADED_BN_IDR'])
            ->whereDate('date', '<=', $date)
            ->orderBy('date', 'desc')
            ->limit($days * 2)
            ->get()
            ->groupBy('date');

        $csv    = "date,foreign_net,market_value\n";
        $byDate = [];

        foreach ($rows as $d => $snaps) {
            $map = $snaps->keyBy('symbol_or_metric');
            $byDate[$d] = [
                'date'         => substr((string)$d, 0, 10), // YYYY-MM-DD only
                'foreign_net'  => (float) ($map->get('FOREIGN_NET_TODAY')->value   ?? 0),
                'market_value' => (float) ($map->get('VALUE_TRADED_BN_IDR')->value ?? 0),
            ];
        }

        ksort($byDate);
        foreach ($byDate as $r) {
            $csv .= "{$r['date']},{$r['foreign_net']},{$r['market_value']}\n";
        }

        $path = $this->tempDir . "/flow_{$date}.csv";
        file_put_contents($path, $csv);
        return $path;
    }

    /**
     * Run a Python script and return the decoded JSON payload.
     * @param string $script   Relative filename inside $scriptsDir (e.g. 'flow.py')
     * @param array  $args     Associative CLI args: ['--input' => '/path', '--output-dir' => '/path']
     * @param string $jsonFile Path to the JSON output file to read
     */
    public function run(string $script, array $args, string $jsonFile): ?array
    {
        $scriptPath = $this->scriptsDir . '/' . $script;
        $cmd = "cd " . escapeshellarg($this->scriptsDir) . " && python3 " . escapeshellarg($scriptPath);

        foreach ($args as $flag => $val) {
            $cmd .= " {$flag} " . escapeshellarg($val);
        }

        $cmd .= " 2>&1";
        $output = shell_exec($cmd);

        \Illuminate\Support\Facades\Log::debug("[PythonBridge] {$script}", ['output' => $output]);

        if (!file_exists($jsonFile)) {
            \Illuminate\Support\Facades\Log::error("[PythonBridge] JSON file not found: {$jsonFile}", ['cmd' => $cmd, 'output' => $output]);
            return null;
        }

        $payload = json_decode(file_get_contents($jsonFile), true);
        return $payload;
    }

    /** Delete temp files */
    public function cleanup(array $paths): void
    {
        foreach ($paths as $p) {
            if (file_exists($p)) {
                @unlink($p);
            }
        }
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }
}
