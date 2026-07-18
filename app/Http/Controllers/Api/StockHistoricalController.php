<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockHistoricalController extends Controller
{
    public function show($code)
    {
        $prices = \App\Models\StockPrice::where('kode', $code)
            ->orderBy('date', 'asc')
            ->get(['date', 'close as value', 'open', 'high', 'low', 'volume']);
            
        return response()->json($prices);
    }
}
