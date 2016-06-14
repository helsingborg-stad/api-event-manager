<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;

    public function __construct($url)
    {
        ini_set('max_execution_time', 300);

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
        echo '<script>location.href = "' . admin_url('edit.php?post_type=event&msg=import-complete') . '";</script>';
    }
}
