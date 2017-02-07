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

    public $id;
    protected $url;
    protected $whitelist;
    protected $options;
    protected $crawler;

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
        $fullReport = new FullReport($this->id, $links);      // Sets status to "processing"
    }
}
