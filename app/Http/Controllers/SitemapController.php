<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Research;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate XML Sitemap for SEO and Google AdSense compliance.
     */
    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://researchavenir.com');

        // Static pages
        $staticPages = [
            ['loc' => $baseUrl . '/',                 'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => $baseUrl . '/artikel',           'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => $baseUrl . '/news',              'changefreq' => 'daily',   'priority' => '0.8'],
            ['loc' => $baseUrl . '/katalog',           'changefreq' => 'weekly',  'priority' => '0.8'],
            ['loc' => $baseUrl . '/tentang',           'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => $baseUrl . '/langganan',         'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/privacy-policy',    'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => $baseUrl . '/syarat-penggunaan', 'changefreq' => 'monthly', 'priority' => '0.4'],
        ];

        // Published articles
        $articles = Article::select(['slug', 'updated_at', 'published_at'])
            ->where('status', 'published')
            ->latest('published_at')
            ->get();

        // Published researches (katalog)
        $researches = Research::select(['slug', 'updated_at', 'date'])
            ->latest('date')
            ->get();

        $xml = $this->buildXml($baseUrl, $staticPages, $articles, $researches);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    private function buildXml(string $baseUrl, array $staticPages, $articles, $researches): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Static pages
        foreach ($staticPages as $page) {
            $xml .= $this->urlEntry(
                $page['loc'],
                now()->toAtomString(),
                $page['changefreq'],
                $page['priority']
            );
        }

        // Articles
        foreach ($articles as $article) {
            $lastmod = $article->updated_at
                ? $article->updated_at->toAtomString()
                : now()->toAtomString();
            $xml .= $this->urlEntry(
                $baseUrl . '/artikel/' . $article->slug,
                $lastmod,
                'weekly',
                '0.8'
            );
        }

        // Katalog / Researches
        foreach ($researches as $research) {
            if (empty($research->slug)) continue;
            $lastmod = $research->updated_at
                ? $research->updated_at->toAtomString()
                : now()->toAtomString();
            $xml .= $this->urlEntry(
                $baseUrl . '/katalog/' . $research->slug,
                $lastmod,
                'monthly',
                '0.7'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "  <url>\n" .
               "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n" .
               "    <lastmod>{$lastmod}</lastmod>\n" .
               "    <changefreq>{$changefreq}</changefreq>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }
}
