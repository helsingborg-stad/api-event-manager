<?php

namespace EventManager\Services\AcfService;

use EventManager\Services\AcfService\Functions\AddOptionsPage;
use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\GetFields;

interface AcfService extends GetField, GetFields, AddOptionsPage
{
}
