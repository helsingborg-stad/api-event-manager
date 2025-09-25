<?php

namespace EventManager\CleanupUnusedTags;

use WpService\Contracts\AddAction;
use WpService\Contracts\DeleteTerm;
use WpService\Contracts\GetTerms;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Contracts\WpDeleteTerm;

class CleanupUnusedTagsTest extends TestCase
{
    /**
     * @testcase cleanupUnusedTags() deletes unused tags
     */
    public function testCleanupUnusedTagsDeletesUnusedTags()
    {
        $wpService         = $this->getWPService();
        $cleanupUnusedTags = new CleanupUnusedTags('category', $wpService);

        $cleanupUnusedTags->cleanupUnusedTags();

        $this->assertCount(1, $wpService->deleteTermCalls);
        $this->assertEquals([1, 'category', []], $wpService->deleteTermCalls[0]);
    }

    private function getWPService(): GetTerms&WpDeleteTerm&AddAction
    {
        return new class implements GetTerms, WpDeleteTerm, AddAction {
            public array $deleteTermCalls = [];

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function getTerms(array|string $args = array(), array|string $deprecated = ""): array|string|WP_Error
            {
                $term          = new \stdClass();
                $term->term_id = 1;
                $term->count   = 0;
                return [$term];
            }

            public function wpDeleteTerm(int $term, string $taxonomy, array|string $args = []): bool|int|WP_Error
            {
                $this->deleteTermCalls[] = [$term, $taxonomy, $args];
                return true;
            }
        };
    }
}
