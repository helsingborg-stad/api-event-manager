<?php

namespace EventManager\AcfSavePostActions;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\SetPostTerms;

class SetPostTermsFromFieldTest extends TestCase
{
    /**
     * @testdox sanitizes array of string term ids into integers
     */
    public function testSanitizesStringTermIdsIntoIntegers()
    {
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => ['1', '2']]);

        $setPostTermsFromField = new SetPostTermsFromField('field_name', 'taxonomy', $wpService, $acfService);
        $setPostTermsFromField->savePost(1);

        $this->assertEquals([1, 2], $wpService->invoked['setPostTerms'][0][1]);
    }

    /**
     * @testdox sanitizes string term id into integer
     */
    public function testSanitizesStringTermIdIntoInteger()
    {
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => '1']);

        $setPostTermsFromField = new SetPostTermsFromField('field_name', 'taxonomy', $wpService, $acfService);
        $setPostTermsFromField->savePost(1);

        $this->assertEquals([1], $wpService->invoked['setPostTerms'][0][1]);
    }

    /**
     * @testdox sanitizes array of string ids into integers
     */
    public function testSanitizesStringIdsIntoIntegers()
    {
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => ['1', '2']]);

        $setPostTermsFromField = new SetPostTermsFromField('field_name', 'taxonomy', $wpService, $acfService);
        $setPostTermsFromField->savePost(1);

        $this->assertEquals([1, 2], $wpService->invoked['setPostTerms'][0][1]);
    }

    /**
     * @testdox converts false into empty array
     */
    public function testConvertsFalseIntoEmptyArray()
    {
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => false]);

        $setPostTermsFromField = new SetPostTermsFromField('field_name', 'taxonomy', $wpService, $acfService);
        $setPostTermsFromField->savePost(1);

        $this->assertEquals([], $wpService->invoked['setPostTerms'][0][1]);
    }

    private function getWpService(): SetPostTerms
    {
        return new class implements SetPostTerms {
            public array $invoked = ['setPostTerms' => []];

            public function setPostTerms(
                int $postId,
                string|array $terms = "",
                string $taxonomy = "post_tag",
                bool $append = false
            ): array|false|\WP_Error {
                $this->invoked['setPostTerms'][] = [$postId, $terms, $taxonomy, $append];
                return [];
            }
        };
    }

    private function getAcfService(array $data): GetField
    {
        return new class ($data) implements GetField {
            public function __construct(private array $data)
            {
            }
            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                return $this->data['getField'];
            }
        };
    }
}
