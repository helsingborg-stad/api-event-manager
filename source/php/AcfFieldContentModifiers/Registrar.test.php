<?php

namespace EventManager\AcfFieldContentModifiers;

use WpService\Contracts\AddAction;
use PHPUnit\Framework\TestCase;

class RegistrarTest extends TestCase
{
    /**
     * @testdox addHooks() should call addAction() for each modifier in the modifiers array.
     */
    public function testAddHooks()
    {
        $modifiers = [$this->getModifier('123')];
        $wpService = $this->getWpService();
        $registrar = new Registrar($modifiers, $wpService);

        $registrar->addHooks();

        $this->assertCount(1, $wpService->addActionCalls);
        $this->assertContains('acf/load_field/key=123', $wpService->addActionCalls);
    }

    private function getModifier(): IAcfFieldContentModifier
    {
        return new class implements IAcfFieldContentModifier {
            public function getFieldKey(): string
            {
                return '123';
            }
            public function modifyFieldContent(array $field): array
            {
                return $field;
            }
        };
    }

    private function getWpService(): AddAction
    {
        return new class implements AddAction {
            public function __construct(public array $addActionCalls = [])
            {
            }

            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                $this->addActionCalls[] = $tag;
                return true;
            }
        };
    }
}
