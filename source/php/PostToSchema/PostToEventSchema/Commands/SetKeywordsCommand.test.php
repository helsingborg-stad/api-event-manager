<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetPostTerms;
use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetKeywordsCommandTest extends TestCase
{
    /**
     * @testdox sets keywords from post terms
     */
    public function testExecute()
    {
        $schema    = new class extends BaseType {
        };
        $postId    = 1;
        $wpService = $this->createMock(GetPostTerms::class);
        $wpService->method('getPostTerms')->willReturn([
            (object) ['name' => 'keyword1'],
            (object) ['name' => 'keyword2'],
        ]);

        $command = new SetKeywordsCommand($schema, $postId, $wpService);
        $command->execute();

        $this->assertEquals(['keyword1', 'keyword2'], $schema->toArray()['keywords']);
    }
}
