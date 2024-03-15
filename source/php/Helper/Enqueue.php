<?php

namespace EventManager\Helper;

use EventManager\Services\WPService\WPService;

abstract class Enqueue implements Hookable
{
    abstract public function getFilename(): string;

    private WPService $wp;

    public function __construct(WPService $wpService)
    {
        $this->wp = $wpService;
    }

    public function addHooks(): void
    {
        $this->wp->addAction('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    private function getType($filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if($extension == "css") {
          return "style";
        }
        if($extension == "js") {
          return "script";
        }

        throw new \Exception('Invalid type enqueued in Enqueue class. Must be either ".js" or ".css"');
    }

    private function getHandle(): string
    {
        $filename = pathinfo($this->getFilename(), PATHINFO_FILENAME);
        $filename = str_replace(['.', '-'], '-', $filename);
        return $filename;
    }

    private function getSource(): string
    {
        return $this->getFilename(); //TODO: Cachebust here. 
    }

    /**
     * Register the script or style
     * 
     * @return void
     * @thows \Exception
     */
    public function enqueue(): void
    {
      $filename = $this->getFilename();

      if($this->getType($filename) === 'script') {
        $this->wp->enqueueScript(
            $this->getHandle(),
            $this->getSource()
        );
      }
      
      if($this->getType($filename) === 'style') {
        $this->wp->enqueueStyle(
            $this->getHandle(),
            $this->getSource()
        );
      }
    }
}
