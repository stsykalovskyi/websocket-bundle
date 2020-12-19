<?php

namespace App\WebsocketBundle\Websocket;

use App\WebsocketBundle\Service\ConnectionManager;
use JMS\Serializer\SerializerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Interface WebsocketInterface
 * @package App\WebsocketBundle\Websocket
 */
interface WebsocketInterface
{
    /**
     * @return mixed
     */
    public function run();


    /**
     * @param RouteCollection $collection
     * @return void
     */
    public function configureRoutes(RouteCollection $collection);

    /**
     * @param Client $client
     * @return void
     */
    public function onConnect(Client $client);

    /**
     * @param Client $client
     * @return void
     */
    public function onError(Client $client);

    /**
     * @param Client $client
     * @param Message $message
     * @return void
     */
    public function onMessage(Client $client, Message $message);


    /**
     * @param Client $client
     * @return void
     */
    public function onDisconnect(Client $client);
}