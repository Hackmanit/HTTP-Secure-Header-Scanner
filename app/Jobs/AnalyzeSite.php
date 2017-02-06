<?php

namespace App\Jobs;

use App\Crawler;
use App\FullReport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Bus\Dispatchable;

class AnalyzeSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $url;
    protected $options;
    protected $crawler;
    protected $fullReport;
    /**
     * @var Collection
     */
    private $whitelist;

    /**
     * Create a new job instance.
     *
     * @param $id
     * @param $url
     * @param Collection $whitelist
     * @param Collection $options
     */
    public function __construct($id, $url, $whitelist, $options)
    {
        $this->id = $id;
        $this->url = $url;
        $this->whitelist = $whitelist;
        $this->options = $options;
        Redis::hset($this->id, 'status', 'queued');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $crawler = new Crawler($this->id, $this->url, $this->whitelist, $this->options);
        $links = $crawler->extractAllLinks();                  // Sets status to "crawling"
        // TODO: Redis extracted links

        $fullReport = new FullReport($this->id, $links);      // Sets status to "processing"
        Redis::hset($this->id, 'reports', serialize($fullReport->rate()));
        Redis::hset($this->id, 'status', 'finished');
    }
}
