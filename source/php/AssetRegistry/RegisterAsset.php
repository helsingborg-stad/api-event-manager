<?php

namespace EventManager\AssetRegistry;

use WpService\WpService;
use EventManager\HooksRegistrar\Hookable;

abstract class RegisterAsset implements Hookable
{
    abstract public function getFilename(): string;
    abstract public function getHandle(): string;

    private WPService $wp;

    public function __construct(WPService $wpService)
    {
        $this->wp = $wpService;
    }

    public function addHooks(): void
    {
        $this->wp->addAction('wp_enqueue_scripts', [$this, 'register']);
    }

    private function getType($filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array($extension, ["css", "js"])) {
            return $extension;
        }

        throw new \Exception('Invalid type enqueued in Enqueue class. Must be either ".js" or ".css"');
    }

    /**
     * Register the script or style
     *
     * @return void
     * @thows \Exception
     */
    public function register(): void
    {
        $filename = $this->getFilename();

        if ($this->getType($filename) === 'js') {
            $this->wp->registerScript(
                $this->getHandle(),
                $this->getFilename()
            );
        }

        if ($this->getType($filename) === 'css') {
            $this->wp->registerStyle(
                $this->getHandle(),
                $this->getFilename()
            );
        }
    }
}
