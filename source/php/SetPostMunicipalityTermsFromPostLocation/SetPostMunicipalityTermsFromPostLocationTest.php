<?php

namespace EventManager\SetPostMunicipalityTermsFromPostLocation;

use AcfService\Contracts\GetField;
use AcfService\Implementations\FakeAcfService;
use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpSetPostTerms;
use WpService\Implementations\FakeWpService;

class SetPostMunicipalityTermsFromPostLocationTest extends TestCase
{
    /**
     * @testdox can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        new SetPostMunicipalityTermsFromPostLocation(new FakeWpService(), new FakeAcfService());
        static::assertTrue(true, 'Could not instantiate');
    }

    /**
     * @testdox attaches to the post_updated hook
     */
    public function testAttachesToThePostUpdatedHook(): void
    {
        $wpService = new FakeWpService(['addAction' => true]);
        $sut       = new SetPostMunicipalityTermsFromPostLocation($wpService, new FakeAcfService());

        $sut->addHooks();

        static::assertSame('post_updated', $wpService->methodCalls['addAction'][0][0]);
    }

    /**
     * @testdox does nothing if acf field conditions are not met
     * @dataProvider invalidAcfFieldProvider
     */
    public function testDoesNothingIfFieldLocationAddressIsNotAvailable($acfField): void
    {
        $wpService  = new FakeWpService();
        $acfService = new FakeAcfService(['getField' => $acfField]);
        $sut        = new SetPostMunicipalityTermsFromPostLocation($wpService, $acfService);

        $sut->postUpdated(123);

        static::assertArrayNotHasKey('wpSetPostTerms', $wpService->methodCalls);
    }

    public static function invalidAcfFieldProvider(): array
    {
        return [
            'null'                     => [null],
            'array without proper key' => [[]],
            'incorrect type'           => [['address_locality' => 321]],
            'empty string'             => [['address_locality' => '']],
            'empty string'             => [['address_locality' => ' ']],
        ];
    }

    /**
     * @testdox sets term if address_locality is available
     */
    public function testSetsTermIfAddressLocalityIsAvailable(): void
    {
        $postId     = 123;
        $wpService  = new FakeWpService(['wpSetPostTerms' => []]);
        $acfService = new FakeAcfService(['getField' => ['address_locality' => 'Test City']]);
        $sut        = new SetPostMunicipalityTermsFromPostLocation($wpService, $acfService);

        $sut->postUpdated($postId);

        static::assertSame($postId, $wpService->methodCalls['wpSetPostTerms'][0][0]); // PostId
        static::assertSame('Test City', $wpService->methodCalls['wpSetPostTerms'][0][1]); // Term
        static::assertSame('municipality', $wpService->methodCalls['wpSetPostTerms'][0][2]); // Taxonomy
    }
}
