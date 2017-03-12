<?php

namespace App\Jobs;

use App\Crawler;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class CrawlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $id;
    protected $url;
    protected $whitelist;
    protected $options;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $url, $whitelist, $options)
    {
        $this->id = $id;
        $this->url = $url;
        $this->whitelist = $whitelist;
        $this->options = $options;

        Redis::hset($id, 'status', 'crawlerQueued');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $crawler = new Crawler($this->id, $this->url, $this->whitelist, $this->options);
        $links = $crawler->extractAllLinks();

        Redis::hset($this->id, 'status', 'crawlerFinished');
        Redis::hset($this->id, 'crawledLinks', serialize($links));
    }
}
