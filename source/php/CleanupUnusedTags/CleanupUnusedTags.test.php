<?php

namespace EventManager\CleanupUnusedTags;

use WpService\Contracts\AddAction;
use WpService\Contracts\DeleteTerm;
use WpService\Contracts\GetTerms;
use PHPUnit\Framework\TestCase;
use WP_Error;

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

    private function getWPService(): GetTerms&DeleteTerm&AddAction
    {
        return new class implements GetTerms, DeleteTerm, AddAction {
            public array $deleteTermCalls = [];

            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return true;
            }

            public function getTerms(array|string $args = array(), array|string $deprecated = ""): array|string|WP_Error
            {
                $term          = new \stdClass();
                $term->term_id = 1;
                $term->count   = 0;
                return [$term];
            }

            public function deleteTerm(int $term, string $taxonomy, array|string $args = array()): bool|int|WP_Error
            {
                $this->deleteTermCalls[] = [$term, $taxonomy, $args];
                return true;
            }
        };
    }
}
