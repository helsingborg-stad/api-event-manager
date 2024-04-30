<?php

namespace EventManager\Helper;

use EventManager\HooksRegistrar\Hookable;
use WpService\WpService;

abstract class Module implements Hookable
{
    protected WPService $wp;

    abstract public function getModuleName(): string;
    abstract public function getModulePath(): string;

    public function __construct(WPService $wpService)
    {
        $this->wp = $wpService;
    }

    public function addHooks(): void
    {
        $this->wp->addAction('init', [$this, 'register']);
        $this->wp->addFilter('/Modularity/externalViewPath', [$this, 'addViewPath']);
    }

    public function addViewPath($paths): array
    {
        $paths['mod-event-form'] = $this->getModulePath() . '/views';
        return $paths;
    }

    public function register(): bool
    {
        //Register the module
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                $this->getModulePath(),
                $this->getModuleName()
            );

            return true;
        }

        throw new \Exception('Modularity not found');
    }
}
