<?php

namespace App\WebsocketBundle\Service;

use App\WebsocketBundle\Websocket\Websocket;
use JMS\Serializer\SerializerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingManager
 * @package App\WebsocketBundle\Service
 */
class RoutingManager
{
    /**
     * @var string
     */
    protected $blackListedBundles = [];

    /**
     * @var string[]
     */
    protected $blackListedWebsocketFiles = [];

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /** @var iterable | Websocket[] */
    private $controllers;
    /** @var string */
    private $projectDir;

    /**
     * RoutingManager constructor.
     */
    public function __construct(iterable $controllers, string $projectDir)
    {
        $this->controllers = $controllers;
        $this->projectDir = $projectDir;
    }


    /**
     * @return RouteCollection|Route[]
     */
    public function findRoutes()
    {
        $allRoutes = new RouteCollection();

        foreach ($this->controllers as $controller) {
            /** @var Route[]|RouteCollection $routes */
            $routes = new RouteCollection();
            $controller->configureRoutes($routes);

            foreach ($routes as $route) {
                $route->setDefault("_websocket", $controller);
            }
            $allRoutes->addCollection($routes);

            $controller->run();
        }

        return $allRoutes;
    }

    /**
     * Get connectionManager value
     * @return ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->connectionManager;
    }

    /**
     * Set connectionManager value
     * @param ConnectionManager $connectionManager
     * @return  $this
     */
    public function setConnectionManager($connectionManager)
    {
        $this->connectionManager = $connectionManager;
        return $this;
    }


}