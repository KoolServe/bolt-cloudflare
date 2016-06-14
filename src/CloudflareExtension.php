<?php

namespace Bolt\Extension\Koolserve\Cloudflare;

use Bolt\Asset\Widget\Widget;
use Bolt\Extension\SimpleExtension;

/**
 * Cloudflare extension class.
 *
 * @author Chris Hilsdon <chris@koolserve.uk
 */
class CloudflareExtension extends SimpleExtension
{
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

    public function backendDashboardWidget()
    {
        return $this->renderTemplate('widget.twig', [
            'day' => 20,
            'week' => 40,
            'month' => 60,
        ]);
    }
}
