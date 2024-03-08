<?php
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modularity\FrontendForm;

use EventManager\Helper\CacheBust;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class Module extends \Modularity\Module
{
    public $slug = 'event-form';
    public $supports = [];

    public function init()
    {
        $this->nameSingular = __('Event Form', );
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');
        //$this->cacheTtl     = 60;
    }
    public function data(): array
    {
      return []; 
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
    }

    public function script()
    {
        wp_enqueue_script(
            'frontend-form',
            EVENT_MANAGER_URL . '/dist/'. CacheBust::name('js/assignment-form.js')
        );
    }

    public function style() {
        wp_enqueue_style(
            'frontend-form',
            EVENT_MANAGER_URL . '/dist/'. CacheBust::name('js/assignment-form.css')
        );
    }
}