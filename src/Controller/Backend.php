<?php

namespace Bolt\Extension\Koolserve\Cloudflare\Controller;

use Bolt\Controller\Zone;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Bolt\Extension\Koolserve\Cloudflare\Traits\FetchData;

class Backend implements ControllerProviderInterface
{
    use FetchData;

    protected $app;

    protected $config;

    /**
     * Constructor.
     *
     * @param config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $this->app = $app;

        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];
        $ctr->value(Zone::KEY, Zone::BACKEND);

        $baseUrl = '/extend/cloudflare';
        $ctr->match($baseUrl, [$this, 'index'])
            ->bind('cloudflare')
            ->method(Request::METHOD_GET)
        ;

       return $ctr;
    }

    /**
     * @param Application $app
     */
    public function index(Application $app)
    {
        $data = [];
        foreach ($this->fetchData() as $k => $v) {
            $totals = $v->result->totals;
            $data[$k] = [
                'requests' => $totals->requests,
                'bandwidth' => $totals->bandwidth,
                'uniques' => $totals->uniques,
            ];
        }

        $html = $app['twig']->render('@CloudflareBackend/index.twig', $data);
        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
