<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\News;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

#[Signature('news:fetch-rss')]
#[Description('Fetch news from external RSS feeds and save to database')]
class FetchRssNews extends Command
{
    /**
     * List of RSS Feeds to fetch from.
     */
    protected $rssFeeds = [
        [
            'source' => 'Antara News',
            'url' => 'https://www.antaranews.com/rss/ekonomi'
        ],
        [
            'source' => 'CNBC Indonesia',
            'url' => 'https://www.cnbcindonesia.com/market/rss'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting RSS News Fetcher...");

        foreach ($this->rssFeeds as $feed) {
            $this->info("Fetching from: {$feed['source']} ({$feed['url']})");

            try {
                // Tambahkan User-Agent untuk menghindari blokir 403 Forbidden
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])->timeout(30)->get($feed['url']);

                if (!$response->successful()) {
                    $this->error("Failed to fetch from {$feed['url']} (Status: {$response->status()})");
                    continue;
                }

                $xmlContent = $response->body();
                // We use simplexml_load_string. To avoid CDATA or namespace issues we use LIBXML_NOCDATA
                $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

                if ($xml === false) {
                    $this->error("Failed to parse XML from {$feed['url']}");
                    continue;
                }

                $count = 0;
                // RSS 2.0 structure typically has channel -> item
                if (isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = trim((string)$item->title);
                        $link = trim((string)$item->link);
                        $description = trim(strip_tags((string)$item->description));
                        $pubDate = trim((string)$item->pubDate);
                        
                        // Parse date
                        $publishedAt = null;
                        if ($pubDate) {
                            try {
                                $publishedAt = Carbon::parse($pubDate);
                            } catch (\Exception $e) {
                                $publishedAt = Carbon::now();
                            }
                        } else {
                            $publishedAt = Carbon::now();
                        }

                        // Try to get image from enclosure or media:content if available
                        $coverImage = null;
                        if (isset($item->enclosure)) {
                            $attributes = $item->enclosure->attributes();
                            if (isset($attributes['url']) && str_starts_with((string)$attributes['type'], 'image/')) {
                                $coverImage = (string)$attributes['url'];
                            }
                        }

                        // Check if news already exists by URL
                        $exists = News::where('source_url', $link)->exists();

                        if (!$exists) {
                            // Ensure unique slug
                            $slug = Str::slug($title);
                            $slugCount = News::where('slug', 'LIKE', $slug . '%')->count();
                            if ($slugCount > 0) {
                                $slug = $slug . '-' . ($slugCount + 1);
                            }

                            // Limit description length for excerpt
                            $excerpt = Str::limit($description, 300);

                            News::create([
                                'title' => $title,
                                'slug' => $slug,
                                'category' => 'Ekonomi/Bisnis',
                                'source' => $feed['source'],
                                'source_url' => $link,
                                'excerpt' => $excerpt,
                                'content' => $description, // Use description as content for now
                                'cover_image' => $coverImage,
                                'published_at' => $publishedAt,
                                'status' => 'published',
                                'is_featured' => false,
                                'is_paid' => false,
                            ]);
                            $count++;
                        }
                    }
                }
                
                $this->info("Successfully fetched and inserted $count new items from {$feed['source']}");

            } catch (\Exception $e) {
                $this->error("Error fetching {$feed['source']}: " . $e->getMessage());
            }
        }

        $this->info("RSS News Fetcher completed.");
    }
}
