<?php

namespace Bolt\Extension\Koolserve\Cloudflare\Traits;

use Cloudflare;

trait FetchData {
    /**
     * Name of the key used for caching the dashbord widget data
     * @var string
     */
    private $cacheKey = 'cloudflaredashboarddata';

    /**
     * The event name used in the log
     * @var string
     */
    private $event = 'extension';

    /**
     * Create a new instance of Cloudflare\Cloudflare and use the guzzle client
     * that is built into bolt
     * @return Cloudflare\Cloudflare instance of Cloudflare\Cloudflare
     */
    protected function newCloudflare() {
        return new Cloudflare\Cloudflare($this->config);
    }

    /**
     * Fetch the data from cloudflare needed for the dashbord widget. Will also
     * cache the response if it was successfull.
     *
     * @return array Website statistics for the last day, week and month
     */
    protected function fetchData()
    {
        $cache = $this->app['cache'];
        $data = $cache->fetch($this->cacheKey);

        //Check to see if we have a cached version
        if ($data) {
            //We do so use it
            //return $data;
        }

        $this->app['logger.system']->info(
            'Getting new data from clodflare',
            ['event' => $this->event]
        );

        $times = [
            'day' => '-1440',
            'week' => '-10080',
            'month' => '-43200',
        ];

        $data = [];
        foreach ($times as $time => $value) {
            $ZoneAnalytics = new Cloudflare\ZoneAnalytics($this->newCloudflare());
            $ZA = $ZoneAnalytics->fetchDashboard(
                $this->config['ZoneID'],
                ['since' => $value]
            );

            if ($ZA != false) {
                $data[$time] = $ZA->getResponse();
            }
        }

        //Store it in the cache for the next hour
        $cache->save($this->cacheKey, $data, 3600);
        $this->app['logger.system']->info(
            'Saved the new data from clodflare for the next hour',
            ['event' => $this->event]
        );

        return $data;
    }
}