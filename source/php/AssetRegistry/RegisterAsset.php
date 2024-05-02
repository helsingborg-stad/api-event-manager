<?php

namespace EventManager\AssetRegistry;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterScript;
use WpService\Contracts\RegisterStyle;

abstract class RegisterAsset implements Hookable
{
    abstract public function getFilename(): string;
    abstract public function getHandle(): string;

    public function __construct(private AddAction&RegisterScript&RegisterStyle $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('wp_enqueue_scripts', [$this, 'register']);
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
            $this->wpService->registerScript(
                $this->getHandle(),
                $this->getFilename()
            );
        }

        if ($this->getType($filename) === 'css') {
            $this->wpService->registerStyle(
                $this->getHandle(),
                $this->getFilename()
            );
        }
    }
}
