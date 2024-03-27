<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetThePostThumbnailUrl;
use PHPUnit\Framework\TestCase;
use WP_Post;

class SetImageTest extends TestCase
{
    /**
     * @testdox sets image from post thumbnail
     */
    public function testExecute()
    {
        $schema    = new \Spatie\SchemaOrg\Thing();
        $wpService = $this->getWpService();

        $command = new SetImage($schema, 1, $wpService);
        $command->execute();

        $this->assertEquals('http://www.foo.bar/baz.jpg', $schema->toArray()['image']);
    }

    private function getWpService(): GetThePostThumbnailUrl
    {
        return new class implements GetThePostThumbnailUrl {
            public function getThePostThumbnailUrl(
                int|WP_Post $postId,
                string|array $size = 'post-thumbnail'
            ): string|false {
                return 'http://www.foo.bar/baz.jpg';
            }
        };
    }
}
