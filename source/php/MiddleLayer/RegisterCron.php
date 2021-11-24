<?php

namespace HbgEventImporter\MiddleLayer;

class RegisterCron
{
    public function __construct()
    {
        add_action('admin_init', function () {
            if (isset($_GET['populatemiddlelayer'])) {
                error_log("populatemiddlelayer");
                $this->schedulePopulate();
            }
        }, 10);

        add_action('populate_middle_layer_api', array($this, 'populateMiddleLayerApi'), 10, 1);
    }

    public function schedulePopulate()
    {
        $classes = ['Guides', 'Navigations'];
        foreach ($classes as $class) {
            wp_schedule_single_event(time(), 'populate_middle_layer_api', array('class' => $class));
        }
    }

    public function populateMiddleLayerApi($className)
    {
        $nameSpace = sprintf('\\HbgEventImporter\MiddleLayer\%s', $className);
        $class = new $nameSpace();
        $class->startPopulate();
    }
}
