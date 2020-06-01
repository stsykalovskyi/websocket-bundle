<?php
/**
 * @author Krzysztof Bednarczyk
 * User: devno
 * Date: 26.02.2016
 * Time: 11:52
 */

namespace Bordeux\WebsocketBundle\Websocket;

use Bordeux\WebsocketBundle\Service\ConnectionManager;
use React\EventLoop\LoopInterface;
use JMS\Serializer\SerializerInterface;

/**
 * Class Websocket
 * @author Krzysztof Bednarczyk
 * @package Bordeux\WebsocketBundle\Websocket
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

    /**
     * @author Krzysztof Bednarczyk
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->connectionManager->getLoop();
    }

    /**
     * @author Krzysztof Bednarczyk
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