<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
        $this->start();
        $this->done();
    }

    /**
     * Used to start the parsing
     */
    abstract public function start();

    public function done()
    {
        echo 'Parser done.';
    }
}
