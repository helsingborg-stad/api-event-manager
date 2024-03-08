<?php

namespace EventManager\Modularity\FrontendForm;

use EventManager\Helper\Module;

class Register extends Module
{
    public function register(): void
    {
      $this->wp->addAction('plugins_loaded', [$this, 'registerModule']);
    }

    public function registerModule(): void
    {
      if (function_exists('modularity_register_module')) {
          modularity_register_module(
            EVENT_MANAGER_PATH . 'source/php/Modules/FrontendForm/',
            'FrontendForm'
          );
      }
    }
}
