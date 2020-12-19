<?php

namespace App\WebsocketBundle\Websocket;

use Ratchet\Server\IoServer;
use Symfony\Component\HttpFoundation\Request;
use Ratchet\ConnectionInterface;

/**
 * Class Client
 * @package App\WebsocketBundle\Websocket
 */
class Client
{

    /**
     * Client id
     *
     * @var string
     */
    protected $id;



    /**
     * HTTP request data
     *
     * @var Request
     */
    protected $request;


    /**
     * Connection from Ratchet
     *
     * @var IoServer
     */
    protected $connection;

    /**
     * Websocket controller
     *
     * @var Websocket
     */
    protected $websocket;


    /**
     * Client constructor.
     * @param string $id
     * @param ConnectionInterface $connection
     * @param WebsocketInterface $websocket
     * @param Request $request
     */
    public function __construct($id, ConnectionInterface $connection, WebsocketInterface $websocket, Request $request)
    {
        $this->id = $id;
        $this->request = $request;
        $this->connection = $connection;
        $this->websocket = $websocket;
    }


    /**
     * Get id value
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get request value
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Message $message
     * @return mixed
     */
    public function sendMessage(Message $message)
    {

        return $this->connection->send(
            $message->getContent()
        );
    }

    /**
     * Get websocket value
     * @return Websocket
     */
    public function getWebsocket()
    {
        return $this->websocket;
    }


    /**
     * @return bool
     */
    public function kill(){
        $this->connection->close();
        return true;
    }
}