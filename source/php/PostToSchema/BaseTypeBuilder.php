<?php

namespace EventManager\PostToSchema;

use Spatie\SchemaOrg\BaseType;

interface BaseTypeBuilder
{
    public function build(): BaseType;
}
