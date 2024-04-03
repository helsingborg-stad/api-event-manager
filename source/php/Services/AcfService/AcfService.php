<?php

namespace EventManager\Services\AcfService;

use EventManager\Services\AcfService\Functions\AddOptionsPage;
use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\AcfService\Functions\RenderFieldSetting;

interface AcfService extends GetField, GetFields, RenderFieldSetting, AddOptionsPage
{
}
