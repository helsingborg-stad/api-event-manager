<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetAccessabilityInformationCommandTest extends TestCase
{
    /**
     * @testdox execute appends accessability information to about
     */
    public function testExecute()
    {
        $meta   = ['accessabilityInformation' => 'Test Accessability Information'];
        $schema = new \Spatie\SchemaOrg\Thing();
        $schema->about('Test About');

        $command = new SetAccessabilityInformation($schema, $meta);
        $command->execute();

        $this->assertStringContainsString('Test About', $schema->toArray()['about']);
        $this->assertStringContainsString('Test Accessability Information', $schema->toArray()['about']);
    }
}
