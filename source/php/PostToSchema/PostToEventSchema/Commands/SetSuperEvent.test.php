<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\Services\WPService\GetPostParent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class SetSuperEventTest extends TestCase
{
    /**
     * @testdox sets super event from post parent
     */
    public function testExecute()
    {
        $schema              = new class extends BaseType {
        };
        $wpService           = $this->getWpService();
        $postToSchemaAdapter = $this->getPostToSchemaAdapter();

        $command = new SetSuperEvent($schema, 1, $wpService, $postToSchemaAdapter);

        $command->execute();

        $this->assertEquals('Super Event', $schema->toArray()['superEvent']['name']);
    }

    private function getWpService(): GetPostParent
    {
        return new class implements GetPostParent {
            public function getPostParent(int|WP_Post|null $postId): ?WP_Post
            {
                $parent = Mockery::mock(WP_Post::class);
                return $parent;
            }
        };
    }

    private function getPostToSchemaAdapter(): IPostToSchemaAdapter
    {
        return new class implements IPostToSchemaAdapter {
            public function getSchema(WP_Post $post): BaseType
            {
                $superEvent = new class extends BaseType{
                };

                $superEvent->name('Super Event');

                return $superEvent;
            }
        };
    }
}
