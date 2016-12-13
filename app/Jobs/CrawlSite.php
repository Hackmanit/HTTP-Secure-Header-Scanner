<?php

namespace App\Jobs;

use App\Crawler;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class CrawlSite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $crawler;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $url, Collection $whitelist, Collection $withOptions)
    {
        $this->id = $id;
        $this->crawler = new Crawler($id, $url, $withOptions, $whitelist);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*
         * Aufgabe / Fahrplan
         * 1. URL eingeben
         * 2. Report erhalten, wie sicher meine Header auf welchen Seiten sind
         * 3. Da das seine Zeit dauert, Wartebildschirm mit Status
         *  3.1 Später wiederkommen / Ticket
         */

        /*
         * TODO:
         * 1. Nutzer gibt URL ein
         * 2. Job wird dispatcht
         * 3. Bei Abarbeitung werden die Responses in Redis gespeichert
         *  3.1 Schon ausgewertet?
         *
         */

        $this->crawler->extractAllLinks();
    }
}
