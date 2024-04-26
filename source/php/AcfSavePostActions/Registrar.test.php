<?php

namespace EventManager\AcfSavepostActions;

use WpService\Contracts\AddAction;
use PHPUnit\Framework\TestCase;

class RegistrarTest extends TestCase
{
    /**
     * @testdox addHooks() should call addAction() for each modifier in the modifiers array.
     */
    public function testAddHooks()
    {
        $actions   = [$this->getSavePostAction()];
        $wpService = $this->getWpService();
        $registrar = new Registrar($actions, $wpService);

        ob_start();
        $registrar->addHooks();

        $this->assertEquals("savePost called with postId: 123", ob_get_clean());
    }

    private function getSavepostAction(): IAcfSavePostAction
    {
        return new class implements IAcfSavePostAction {
            public function savePost(int $postId): void
            {
                echo "savePost called with postId: {$postId}";
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
                $function_to_add(123);
                return true;
            }
        };
    }
}
