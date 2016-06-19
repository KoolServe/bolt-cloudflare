<?php

namespace Bolt\Extension\Koolserve\Cloudflare;

use Bolt\Asset\Widget\Widget;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;

/**
 * Cloudflare extension class.
 *
 * @author Chris Hilsdon <chris@koolserve.uk>
 */
class CloudflareExtension extends SimpleExtension
{
    use Traits\FetchData;

    /**
     * The even name used in the log
     * @var string
     */
    private $event = 'extension';

    protected function registerAssets()
    {
        //Create a new dashbord widget. Use dashboard_aside_bottom to aviod
        //conflicting with bobdenotter/seo.
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

    protected function registerMenuEntries()
    {
        $menu = new MenuEntry('cloudflare-menu', 'cloudflare');
        $menu
            ->setLabel('Cloudflare')
            ->setIcon('fa:cloud')
            ->setPermission('settings')
        ;

        return [
            $menu,
        ];
    }

    protected function registerBackendControllers()
    {
        return [
            '/' => new Controller\Backend(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates/backend' => ['position' => 'append', 'namespace' => 'CloudflareBackend'],
        ];
    }

    /**
     * Render the backend dashboard widget
     */
    public function backendDashboardWidget()
    {
        $data = [];
        foreach ($this->fetchData() as $k => $v) {
            $data[$k] = $v->result->totals->requests->all;
        }

        return $this->renderTemplate('widget.twig', $data);
    }
}
