<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
        $this->start();
    }

    /**
     * Used to start the parsing
     */
    abstract public function start();
}
