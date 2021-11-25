<?php

namespace HbgEventImporter\MiddleLayer;

class RegisterCron
{
    public function __construct()
    {
        add_action('wp_ajax_schedule_populate_middle_layer', array($this, 'schedulePopulate'));
        add_action('populate_middle_layer_api', array($this, 'populateMiddleLayerApi'), 10, 1);
    }

    public function schedulePopulate()
    {
        $classes = ['Languages', 'Navigations', 'GuideGroups', 'Guides'];
        foreach ($classes as $class) {
            wp_clear_scheduled_hook('populate_middle_layer_api', array('class' => $class));
            wp_schedule_single_event(time(), 'populate_middle_layer_api', array('class' => $class));
        }
        wp_die();
    }

    public function populateMiddleLayerApi($className)
    {
        $nameSpace = sprintf('\\HbgEventImporter\MiddleLayer\%s', $className);
        $class = new $nameSpace();
        $class->startPopulate();
    }
}
