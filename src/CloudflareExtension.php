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
    public function initialize()
    {
       var_dump($this->app, 'Uno');
       exit();
    }

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
            ->setLocation('dashboard_aside_middle')
            ->setCallback([$this, 'backendDashboardWidget'])
            ->setCallbackArguments([])
            ->setDefer(false);

        $assets[] = $widgetObj;

        return $assets;
    }

    protected function fetchData()
    {
        $config = $this->getConfig();

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

        return $data;
    }

    public function backendDashboardWidget()
    {
        $data = $this->fetchData();
        return $this->renderTemplate('widget.twig', $data);
    }
}
