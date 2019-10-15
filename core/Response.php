<?php

namespace Core;

class Response
{
    CONST TEXT_HTML = 'text/html';
    CONST APPLICATION_JSON = 'application/json';

    private $status;
    protected $content;
    protected $headers = [];

    public function __construct($content, int $status = 200)
    {
        $this->status = $status;
        $this->content = $content;

        $this->setContentTypeHeader();

        return $this;
    }

    protected function setContentTypeHeader()
    {
        if (is_array($this->content)) {
            $this->header('Content-Type', self::APPLICATION_JSON);
        }
        // default is text/html
    }

    public function header($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function sendHeaders()
    {
        foreach ($this->headers as $name => $value) {
            header("$name: $value", true, $this->status);
        }
    }

    protected function shouldBeJson()
    {
        return $this->headers['Content-Type'] === self::APPLICATION_JSON;
    }

    public function sendContent()
    {
        echo $this->shouldBeJson() ? json_encode($this->content) : $this->content;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }
}
