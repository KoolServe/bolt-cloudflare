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

    protected $app;

    protected $config;

    public function before()
    {
        $this->app = $this->getContainer();
        $this->config = $this->getConfig();
    }

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
        $config = $this->getConfig();

        return [
            '/' => new Controller\Backend($config),
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
        $app = $this->getContainer();
        $data = [];
        foreach ($this->fetchData($app) as $k => $v) {
            $data[$k] = $v->result->totals->requests->all;
        }

        return $this->renderTemplate('widget.twig', $data);
    }
}
