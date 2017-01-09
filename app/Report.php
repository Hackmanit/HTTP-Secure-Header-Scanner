<?php

namespace App;


use Illuminate\Support\Facades\Redis;


/*
 * Was hab ich eigentlich vor damit?
 *
 */

class Report
{
    protected $id;
    protected $crawledUrls;
    protected $overAllRating;

    /**
     * Report constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->crawledUrls = Redis::hget($id, "crawledUrls");

        Redis::hset($id, "reportFinished", true);
    }


}