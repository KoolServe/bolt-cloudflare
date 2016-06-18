<?php

namespace Bolt\Extension\Koolserve\Cloudflare\Controller;

use Bolt\Controller\Zone;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Backend implements ControllerProviderInterface
{
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
     * @param Request     $request
     */
    public function index(Application $app, Request $request)
    {
        $html = $app['twig']->render('@CloudflareBackend/index.twig', []);
        return new Response(new \Twig_Markup($html, 'UTF-8'));
    }
}
