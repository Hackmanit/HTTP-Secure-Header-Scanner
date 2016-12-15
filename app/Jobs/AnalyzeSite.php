<?php

namespace App\Jobs;

use App\Crawler;
use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class AnalyzeSite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $url;
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
    public function __construct($id, $url, Collection $whitelist, Collection $options, $limit = null)
    {
        $this->id = $id;
        $this->crawler = new Crawler($id, $url, $whitelist, $options, $limit);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->crawler->extractAllLinks();
        $report = new Report($this->id);
    }
}
