<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\AcfService\Functions\GetFields;
use PHPUnit\Framework\TestCase;

class SetTypicalAgeRangeTest extends TestCase
{
    /**
     * @testdox sets typical age range from audience term meta fields
     */
    public function testExecute()
    {
        $audience = new \Spatie\SchemaOrg\Audience();
        $audience->identifier(1);
        $schema = new \Spatie\SchemaOrg\Thing();
        $schema->audience($audience);
        $acfService = $this->getAcfService();
        $command    = new SetTypicalAgeRange($schema, $acfService);

        $command->execute();

        $this->assertEquals('18-25', $schema->toArray()['typicalAgeRange']);
    }

    private function getAcfService(): GetFields
    {
        return new class implements GetFields {
            public function getFields(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array
            {
                return [
                    'typicalAgeRangeStart' => 18,
                    'typicalAgeRangeEnd'   => 25
                ];
            }
        };
    }
}
