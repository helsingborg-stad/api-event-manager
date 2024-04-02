<?php

namespace EventManager\ContentExpirationManagement;

use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\WPService\GetPosts;
use PHPUnit\Framework\TestCase;

class ExpiredEventsTest extends TestCase
{
    /**
     * @testdox eventHasExpired() returns post id of expired event
     */
    public function testEventHasExpiredReturnsTrueIfLatestDateIsMoreThanAMonthAgo()
    {
        $fields = [ 1 => ['occasions' => [['date' => '2021-01-01']]] ];

        $expirationTimestamp = strtotime('2021-04-01');
        $acfService          = $this->getAcfService($fields);
        $wpService           = $this->getWpService();
        $expiredEvents       = new ExpiredEvents($expirationTimestamp, $wpService, $acfService);

        $this->assertContains(1, $expiredEvents->getExpiredPosts());
    }

    /**
    * @testdox getExpiredPosts() returns an empty array if the latest date has not passed expirationTimestamp
     */
    public function testEventHasExpiredReturnsFalseIfLatestDateIsLessThanAMonthAgo()
    {
        $fields = [ 1 => ['occasions' => [['date' => '2021-04-02']]] ];

        $expirationTimestamp = strtotime('2021-04-01');
        $acfService          = $this->getAcfService($fields);
        $wpService           = $this->getWpService();
        $expiredEvents       = new ExpiredEvents($expirationTimestamp, $wpService, $acfService);

        $this->assertEmpty($expiredEvents->getExpiredPosts());
    }

    /**
     * @testdox getExpiredPosts() returns an empty array if no occasions are found
     */
    public function testEventHasExpiredReturnsFalseIfNoOccasionsFound()
    {
        $fields = [ 1 => ['occasions' => []] ];

        $expirationTimestamp = strtotime('2021-04-01');
        $acfService          = $this->getAcfService($fields);
        $wpService           = $this->getWpService();
        $expiredEvents       = new ExpiredEvents($expirationTimestamp, $wpService, $acfService);

        $this->assertEmpty($expiredEvents->getExpiredPosts());
    }

    private function getWpService(): GetPosts
    {
        return new class implements GetPosts {
            public function getPosts(array $args): array
            {
                return [1];
            }
        };
    }

    private function getAcfService(array $fields): GetField
    {

        return new class ($fields) implements GetField {
            public function __construct(private array $fields)
            {
            }

            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return $this->fields[$postId][$selector] ?? null;
            }
        };
    }
}
