<?php

namespace App\WebsocketBundle\Service;

use App\WebsocketBundle\Websocket\Client;
use App\WebsocketBundle\Websocket\Message;
use App\WebsocketBundle\Websocket\WebsocketInterface;
use Guzzle\Http\Message\Header;
use GuzzleHttp\Message\Request as RequestGuzzle;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ConnectionManager
 * @package App\WebsocketBundle\Service
 */
class ConnectionManager implements MessageComponentInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $clients;

    /**
     * @var RouteCollection|Route[]
     */
    protected $routes;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var LoopInterface
     */
    protected $loop;

    private $logger;

    /**
     * ConnectionManager constructor.
     */
    public function __construct(LoggerInterface $wsLogger)
    {
        $this->clients  = new SplObjectStorage();
        $this->logger   = $wsLogger;
    }

    /**
     * Get routes value
     * @return \Symfony\Component\Routing\Route[]|RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set routes value
     * @param \Symfony\Component\Routing\Route[]|RouteCollection $routes
     * @return  $this
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  \Ratchet\Server\IoServer $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {

        try {
            $request = $this->createRequest($conn);
            $context = new RequestContext('/');
            $context->fromRequest($request);
            $matcher = new UrlMatcher($this->routes, $context);

            $params = $matcher->matchRequest(
                $request
            );

            if (is_array($params) && $params['_websocket'] instanceof WebsocketInterface) {
                foreach ($params as $attr => $val) {
                    $request->attributes->set($attr, $val);
                }
                $client = new Client(uniqid(), $conn, $params['_websocket'], $request);
                $conn->__bclient = $client;
                $client->getWebsocket()->onConnect($client);
                return;
            }
            //not found
            $conn->close();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        /** @var Client $client */
        $client = isset($conn->__bclient) ? $conn->__bclient : null;
        if (!($client instanceof Client)) {
            $conn->close();
            return;
        }

        $client->getWebsocket()->onDisconnect(
            $client
        );
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        /** @var Client $client */
        $client = isset($conn->__bclient) ? $conn->__bclient : null;
        if (!($client instanceof Client)) {
            $conn->close();
            return;
        }

        $client->getWebsocket()->onError(
            $client
        );
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg)
    {
        if ($msg == "ping") {
            $conn->send("pong");
            return;
        }


        /** @var Client $client */
        $client = isset($conn->__bclient) ? $conn->__bclient : null;
        if (!($client instanceof Client)) {
            $conn->close();
            return;
        }

        $client->getWebsocket()->onMessage(
            $client,
            new Message($msg)
        );
    }

    /**
     * Set output value
     * @param OutputInterface $output
     * @return  $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Get output value
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get loop value
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Set loop value
     * @param LoopInterface $loop
     * @return  $this
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
        return $this;
    }

    /**
     * @param ConnectionInterface $conn
     * @return Request
     */
    public function createRequest(ConnectionInterface $conn)
    {
        /** @var \GuzzleHttp\Psr7\Request $requestOrg */
        $requestOrg = $conn->httpRequest;
        parse_str($requestOrg->getUri()->getQuery(), $query);
        $server = [
            'SERVER_NAME'       => $requestOrg->getUri()->getHost(),
            'REMOTE_ADDR'       => $conn->remoteAddress,
            'REQUEST_SCHEME'    => $requestOrg->getUri()->getScheme(),
            'REQUEST_URI'       => (string)$requestOrg->getUri(),
        ];


        /** @var Header $item */
        foreach ($requestOrg->getHeaders() as $key => $item) {
            $server[strtoupper("http_{$key}")] = $item[0];
        }


        $cookies = [];
        if ($requestOrg->hasHeader('Cookies')) {
            $headerCookie =  $requestOrg->getHeader("Cookie");
            foreach (explode(";", $headerCookie) as $item) {
                if (!($item = trim($item))) {
                    continue;
                }
                list($name, $value) = explode("=", trim($item));
                $cookies[$name] = urldecode($value);
            }
        }

        return new Request(
            $query, //get
            [], //post
            [], //attributes
            $cookies, //cookies
            [], //files, empty
            $server,
            null
        );

    }

    /**
     * @param \Exception $exception
     * @return $this
     */
    public function handleException(\Exception $exception)
    {

        $table = new Table($this->getOutput());

        $table->addRow([
            "Message",
            $exception->getMessage()
        ]);

        $table->addRow(new TableSeparator());
        $table->addRow([
            "File",
            "{$exception->getFile()}:{$exception->getLine()}"
        ]);


        $table->render();

        $this->getOutput()->writeln($exception->getTraceAsString());
        return $this;
    }
}
