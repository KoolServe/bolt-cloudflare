<?php

namespace Bolt\Extension\Koolserve\Cloudflare;

use Bolt\Asset\Widget\Widget;
use Bolt\Extension\SimpleExtension;
use Cloudflare;

/**
 * Cloudflare extension class.
 *
 * @author Chris Hilsdon <chris@koolserve.uk
 */
class CloudflareExtension extends SimpleExtension
{
    private $cacheKey = 'cloudflaredashboarddata';
    private $event = 'Cloudflare';

    protected function newCloudflare() {
        $config = $this->getConfig();
        $app = $this->getContainer();

        return new Cloudflare\Cloudflare($config, $app['guzzle.client']);
    }

    protected function registerAssets()
    {
        $widgetObj = new Widget();
        $widgetObj
            ->setZone('backend')
            ->setLocation('dashboard_aside_bottom')
            ->setCallback([$this, 'backendDashboardWidget'])
            ->setCallbackArguments([])
            ->setDefer(false);

        $assets[] = $widgetObj;

        return $assets;
    }

    protected function fetchData()
    {
        $config = $this->getConfig();
        $app = $this->getContainer();
        $cache = $app['cache'];
        $data = $cache->fetch($this->cacheKey);

        //Check to see if we have a cached version
        if($data) {
            //We do so use it
            return $data;
        }

        $app['logger.system']->info(
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
                $config['ZoneID'],
                ['since' => $value]
            );

            if($ZA != false) {
                $total = $ZA->getTotalRequests();
                $data[$time] = $total->all;
            }
        }

        //Store it iin the cache for the next hour
        $cache->save($this->cacheKey, $data, 3600);
        $app['logger.system']->info(
            'Saved the new data from clodflare for the next hour',
            ['event' => $this->event]
        );

        return $data;
    }

    public function backendDashboardWidget()
    {
        $data = $this->fetchData();
        return $this->renderTemplate('widget.twig', $data);
    }
}
