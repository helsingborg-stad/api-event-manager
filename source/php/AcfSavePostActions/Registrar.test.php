<?php

namespace EventManager\AcfSavePostActions;

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
            public function savePost(int|string $postId): void
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

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $callback(123);
                return true;
            }
        };
    }
}
