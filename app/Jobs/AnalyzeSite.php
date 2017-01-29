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

class AnalyzeSite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $url;
    protected $options;
    protected $crawler;
    protected $fullReport;

    /**
     * Create a new job instance.
     *
     * @param $id
     * @param $url
     * @param Collection $whitelist
     * @param Collection $options
     */
    public function __construct($id, $url, Collection $whitelist, Collection $options)
    {
        $this->id = $id;
        Redis::hset($this->id, 'status', 'queued');
        $this->crawler = new Crawler($id, $url, $whitelist, $options);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->crawler->extractAllLinks();                  // Sets status to "crawling"
        $this->fullReport = new FullReport($this->id);      // Sets status to "processing"

        Redis::hset($this->id, 'reports', serialize($this->fullReport->get()));
        Redis::hset($this->id, 'status', 'finished');
    }
}
