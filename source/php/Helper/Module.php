<?php

namespace EventManager\Helper;

use EventManager\Services\WPService\WPService;

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
    }

    public function register(): bool
    {
      add_filter('/Modularity/externalViewPath', function($paths) {
          $paths[] = $this->getModulePath() . '/views';
          return $paths;
      });

      //TODO: Make this use abstract config functions
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
