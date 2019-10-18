<?php

namespace Core;

/**
 * Class Response
 * @package Core
 * @property-read int status
 * @property-read mixed content
 * @property-read array headers
 */
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
        } else {
            $this->header('Content-Type', self::TEXT_HTML);
        }
    }

    public function header($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    public function sendHeaders()
    {
        foreach ($this->headers as $name => $value) {
            header("$name: $value", true, $this->status);
        }
    }

    protected function shouldBeJson()
    {
        if (isset($this->headers['Content-Type'])) {
            return $this->headers['Content-Type'] === self::APPLICATION_JSON;
        }

        return false;
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
