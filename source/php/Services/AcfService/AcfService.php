<?php

namespace EventManager\Services\AcfService;

use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\RenderFieldSetting;

interface AcfService extends 
GetField,
RenderFieldSetting
{
}
