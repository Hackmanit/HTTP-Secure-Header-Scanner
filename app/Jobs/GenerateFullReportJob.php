<?php

namespace App\Jobs;

use App\FullReport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class GenerateFullReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $listOfUrls;

    /**
     * Create a new job instance.
     *
     * @param $id
     * @param Collection $listOfUrls
     */
    public function __construct($id, Collection $listOfUrls)
    {
        $this->id = $id;
        $this->listOfUrls = $listOfUrls;
        Redis::hset($id, 'status', 'queued');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fullReport = (new FullReport($this->id, $this->listOfUrls))->generate();      // Sets status to "processing"
        Redis::hset($this->id, "fullReport", serialize($fullReport));
        Redis::hset($this->id, 'status', 'finished');
    }
}
