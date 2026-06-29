<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeskBrief;
use App\Models\DeskBriefDriver;
use App\Models\SmartMoneyFlow;

class DeskBriefDataSeeder extends Seeder
{
    public function run()
    {
        $brief = DeskBrief::orderBy('date', 'desc')->first();
        
        if ($brief) {
            DeskBriefDriver::updateOrCreate(
                ['brief_id' => $brief->id, 'title' => 'Disinflation + Growth'],
                ['category' => 'Macro', 'impact_level' => 'High', 'rank' => 1]
            );
            DeskBriefDriver::updateOrCreate(
                ['brief_id' => $brief->id, 'title' => 'Domestic-revenue tilt'],
                ['category' => 'Macro', 'impact_level' => 'Medium', 'rank' => 2]
            );
            DeskBriefDriver::updateOrCreate(
                ['brief_id' => $brief->id, 'title' => 'Foreign inflow'],
                ['category' => 'Flow', 'impact_level' => 'High', 'rank' => 3]
            );
        }

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        
        SmartMoneyFlow::updateOrCreate(
            ['date' => $yesterday],
            ['cumulative_net' => '-200M', 'cumulative_vs' => 'Sell', 'cost_basis' => '4000', 'price_vs_cost' => 'Below']
        );
        SmartMoneyFlow::updateOrCreate(
            ['date' => $today],
            ['cumulative_net' => '500M', 'cumulative_vs' => 'Buy', 'cost_basis' => '4100', 'price_vs_cost' => 'Above']
        );
    }
}
