<?php

namespace EventManager\PostToSchema;

use ArrayAccess;
use JsonSerializable;
use Spatie\SchemaOrg\Type;

interface ISchema extends Type, ArrayAccess, JsonSerializable
{
}
