<?php

namespace App\Jobs;

use App\DOMXSSCheck;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ScanStartRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DomxssScanJob implements ShouldQueue
{
    protected $url;
    protected $callbacks;


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, array $callbacks)
    {

        $this->url = $url;
        $this->callbacks = $callbacks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = (new DomxssCheck($this->url))->report();
        ApiController::notifyCallbacks($this->callbacks, $report);
    }
}
