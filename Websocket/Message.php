<?php

namespace App\WebsocketBundle\Websocket;

/**
 * Class Message
 * @package App\WebsocketBundle\Websocket
 */
class Message
{
    /**
     * @var null|string
     */
    protected $content;

    /**
     * Message constructor.
     * @param string $content
     */
    public function __construct($content = null)
    {
        $this->content = $content;
    }


    /**
     * Get content value
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content value
     * @param null|string $content
     * @return  $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }


}