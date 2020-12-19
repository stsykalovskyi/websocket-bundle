<?php

namespace App\WebsocketBundle\Websocket;

use App\WebsocketBundle\Service\ConnectionManager;
use React\EventLoop\LoopInterface;
use JMS\Serializer\SerializerInterface;

/**
 * Class Websocket
 * @package App\WebsocketBundle\Websocket
 */
abstract class Websocket implements WebsocketInterface
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->setConnectionManager($connectionManager);
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

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->connectionManager->getLoop();
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->connectionManager->getOutput();
    }

    public function getSerializer()
    {
        return $this->serializer;
    }
}