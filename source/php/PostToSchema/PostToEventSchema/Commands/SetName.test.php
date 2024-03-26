<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Post;

class SetNameCommandTest extends TestCase
{
    /**
     * @testdox sets name from post title.
     */
    public function testExecute()
    {
        $post             = Mockery::mock(WP_Post::class);
        $post->post_title = 'Test Title';
        $schema           = new \Spatie\SchemaOrg\Thing();

        $command = new SetName($schema, $post);
        $command->execute();

        $this->assertEquals('Test Title', $schema->toArray()['name']);
    }
}
