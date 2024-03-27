<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Post;

class SetIdentifierTest extends TestCase
{
    /**
     * @testdox sets @id from post id.
     */
    public function testExecute()
    {
        $post     = Mockery::mock(WP_Post::class);
        $post->ID = 1;
        $schema   = new \Spatie\SchemaOrg\Thing();

        $command = new SetIdentifier($schema, $post);
        $command->execute();

        $this->assertEquals(1, $schema->toArray()['@id']);
    }
}
