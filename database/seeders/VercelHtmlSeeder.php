<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\Research;

class VercelHtmlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Syncing Vercel HTML data...');
        Artisan::call('avenir:sync-html');
        $this->command->info(Artisan::output());

        $this->command->info('Parsing katalog.html to update Research data...');
        $this->parseAndSeedKatalog();
    }

    private function parseAndSeedKatalog()
    {
        $katalogPath = storage_path('app/website/katalog.html');
        if (!file_exists($katalogPath)) {
            $this->command->error("File katalog.html tidak ditemukan di {$katalogPath}");
            return;
        }

        $html = file_get_contents($katalogPath);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        $cards = $xpath->query('//div[contains(@class, "card")]');
        $count = 0;

        foreach ($cards as $card) {
            if (!$card->hasAttribute('data-date')) continue;

            $date = $card->getAttribute('data-date');
            $tags = $card->getAttribute('data-tags');

            $titleNode = $xpath->query('.//h2', $card)->item(0);
            $title = $titleNode ? $titleNode->nodeValue : '';

            $subtitleNode = $xpath->query('.//p[contains(@class, "card-sub")]', $card)->item(0);
            $subtitle = '';
            if ($subtitleNode) {
                $subtitleHtml = '';
                foreach ($subtitleNode->childNodes as $child) {
                    $subtitleHtml .= $dom->saveHTML($child);
                }
                $subtitle = trim($subtitleHtml);
            }

            $tickerNode = $xpath->query('.//span[contains(@class, "ticker")]', $card)->item(0);
            $ticker = $tickerNode ? trim(str_replace('IDX:', '', $tickerNode->nodeValue)) : null;

            $sectorNode = $xpath->query('.//span[contains(@class, "sector")]', $card)->item(0);
            $sectorHtml = '';
            if ($sectorNode) {
                foreach ($sectorNode->childNodes as $child) {
                    $sectorHtml .= $dom->saveHTML($child);
                }
            }
            $sector = trim($sectorHtml);

            $cmvNodes = $xpath->query('.//div[contains(@class, "card-m")]//div[contains(@class, "cmv")]', $card);
            $revenue = $cmvNodes->item(0) ? trim($cmvNodes->item(0)->nodeValue) : null;
            $patmi = $cmvNodes->item(1) ? trim($cmvNodes->item(1)->nodeValue) : null;
            $sales = $cmvNodes->item(2) ? trim($cmvNodes->item(2)->nodeValue) : null;

            $btnNode = $xpath->query('.//button[contains(@class, "cta-unlock")]', $card)->item(0);
            $price = $btnNode ? $btnNode->getAttribute('data-price') : null;
            $slug = null;
            if ($btnNode) {
                $onclick = $btnNode->getAttribute('onclick');
                if (preg_match("/'([^']+)\.html'/", $onclick, $matches)) {
                    $slug = $matches[1];
                }
            }

            if (!$slug) {
                // Generate slug if not found
                if ($ticker) {
                    $parts = explode(':', $ticker);
                    $slug  = strtolower(trim(end($parts)));
                    $slug  = preg_replace('/[^a-z0-9]/', '', $slug);
                } else {
                    $slug = \Illuminate\Support\Str::slug($title);
                    $slug = preg_replace('/^pt-/', '', $slug);
                    $slug = preg_replace('/-tbk$/', '', $slug);
                    $slug = preg_replace('/-+/', '-', trim($slug, '-'));
                }
            }

            Research::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'    => $title,
                    'subtitle' => $subtitle,
                    'ticker'   => $ticker,
                    'sector'   => $sector,
                    'revenue'  => $revenue,
                    'patmi'    => $patmi,
                    'sales'    => $sales,
                    'price'    => $price,
                    'tags'     => $tags,
                    'date'     => $date,
                ]
            );
            $count++;
        }

        $this->command->info("Successfully updated {$count} researches from katalog.html.");
    }
}
