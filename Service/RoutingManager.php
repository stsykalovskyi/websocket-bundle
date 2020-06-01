<?php
/**
 * @author Krzysztof Bednarczyk
 * User: devno
 * Date: 26.02.2016
 * Time: 10:20
 */

namespace Bordeux\WebsocketBundle\Service;

use Bordeux\WebsocketBundle\Websocket\Websocket;
use JMS\Serializer\SerializerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingManager
 * @author Krzysztof Bednarczyk
 * @package Bordeux\WebsocketBundle\Service
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
     * @author Krzysztof Bednarczyk
     */
    public function __construct(iterable $controllers, string $projectDir)
    {
        $this->controllers = $controllers;
        $this->projectDir = $projectDir;
    }


    /**
     * @author Krzysztof Bednarczyk
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
     * @author Krzysztof Bednarczyk
     * @return ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->connectionManager;
    }

    /**
     * Set connectionManager value
     * @author Krzysztof Bednarczyk
     * @param ConnectionManager $connectionManager
     * @return  $this
     */
    public function setConnectionManager($connectionManager)
    {
        $this->connectionManager = $connectionManager;
        return $this;
    }


}