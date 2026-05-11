<?php

use AcfService\Implementations\NativeAcfService;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetTerm;
use WpService\Contracts\GetTermBy;
use WpService\Contracts\GetUserBy;
use WpService\Contracts\WpGetPostTerms;
use WpService\Contracts\WpInsertPost;
use WpService\Contracts\WpSetCurrentUser;
use WpService\Contracts\WpUpdatePost;
use WpService\Implementations\NativeWpService;

class CreateOrganizationAdminWhenOrganizationIsCreatedTest extends WP_UnitTestCase {

	/**
	 * @testdox Organization admin is created when a term is created and the email field is set
	 */
	public function testOrganizationAdminIsCreated(): void {
		$organizerEmail = $this->generateOrganizerEmail();
		$wpService = static::createWpService();
		$acfService = new NativeAcfService();
		$adminUserId = $this->factory()->user->create(['role' => 'administrator']);
		$wpService->wpSetCurrentUser($adminUserId);

		static::assertFalse(
			$wpService->getUserBy('email', $organizerEmail),
			'A user with the email ' . $organizerEmail . ' already exists. Please run the test again.'
		);

		$postId = $wpService->wpInsertPost($this->getDraftEventPostData());

		$acfService->updateField('submitNewOrganization', true, $postId);
		$acfService->updateField('newOrganizers', [$this->getOrganizerData($organizerEmail)], $postId);

		$wpService->wpUpdatePost([
			'ID' => $postId,
			'post_status' => 'publish',
		]);


		$wpService->doAction('acf/save_post', $postId);

		$termId = $wpService->wpGetPostTerms($postId, 'organization')[0]->term_id;
		$user = $wpService->getUserBy('email', $organizerEmail);
		static::assertInstanceOf(WP_User::class, $user, 'No user was created');
		static::assertSame(['organization_administrator' => true], $user->caps, 'The user does not have the correct role');
		static::assertContains($termId, $acfService->getField('organizations', 'user_' . $user->ID) ?? [], 'The organization term ID was not added to the user');
	}

	private function generateOrganizerEmail(): string {
		return 'test' . random_int(1000, 9999) . '@example.com';
	}

	/** @return array{post_type: string, post_title: string, post_status: string} */
	private function getDraftEventPostData(): array {
		return [
			'post_type' => 'event',
			'post_title' => 'Test Event',
			'post_status' => 'draft',
		];
	}

	/** @return array<string, string> */
	private function getOrganizerData(string $organizerEmail): array {
		return [
			'organizerName' => 'Test Organizer',
			'organizerContact' => 'John Doe',
			'organizerEmail' => $organizerEmail,
			'organizerTelephone' => '123-456-7890',
			'organizerAddress' => '123 Test St, Test City, TX 12345',
			'organizerUrl' => 'https://www.testorganizer.com',
		];
	}

	private static function createWpService(): WpInsertPost&GetTerm&GetUserBy&WpUpdatePost&WpSetCurrentUser&DoAction&WpGetPostTerms {
		return new NativeWpService();
	}
}
