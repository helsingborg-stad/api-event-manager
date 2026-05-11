<?php

use AcfService\Implementations\NativeAcfService;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetUserBy;
use WpService\Contracts\WpGetPostTerms;
use WpService\Contracts\WpInsertPost;
use WpService\Contracts\WpSetCurrentUser;
use WpService\Contracts\WpUpdatePost;
use WpService\Implementations\NativeWpService;

class OrganizationAdministratorWelcomeEmailWhenOrganizationIsCreatedTest extends WP_UnitTestCase {

private array $sentEmails = [];

/**
 * @testdox Creating organization administrator through organization creation sends custom welcome email
 */
public function testCreatingOrgAdminSendsCustomWelcomeEmail(): void {
$organizerEmail = $this->generateOrganizerEmail();
$wpService = static::createWpService();
$acfService = new NativeAcfService();
$adminUserId = $this->factory()->user->create(['role' => 'administrator']);
$wpService->wpSetCurrentUser($adminUserId);

add_filter('pre_wp_mail', [$this, 'captureEmail'], 10, 2);

$postId = $wpService->wpInsertPost($this->getDraftEventPostData());

$acfService->updateField('submitNewOrganization', true, $postId);
$acfService->updateField('newOrganizers', [$this->getOrganizerData($organizerEmail)], $postId);

$wpService->wpUpdatePost([
'ID' => $postId,
'post_status' => 'publish',
]);

$wpService->doAction('acf/save_post', $postId);

remove_filter('pre_wp_mail', [$this, 'captureEmail'], 10);

$organizationTerms = $wpService->wpGetPostTerms($postId, 'organization');
static::assertNotEmpty($organizationTerms, 'No organization term was created for the post');

$termId = $organizationTerms[0]->term_id;
$user = $wpService->getUserBy('email', $organizerEmail);

static::assertInstanceOf(WP_User::class, $user, 'No user was created');
static::assertSame(['organization_administrator' => true], $user->caps, 'The user does not have the correct role');
static::assertContains($termId, $acfService->getField('organizations', 'user_' . $user->ID) ?? [], 'The organization term ID was not added to the user');

static::assertCount(1, $this->sentEmails);
static::assertSame($organizerEmail, $this->sentEmails[0]['to']);
static::assertStringContainsString('Welcome to', $this->sentEmails[0]['subject']);
static::assertStringContainsString('organization administrator account is now active', $this->sentEmails[0]['message']);
static::assertStringNotContainsString('Username:', $this->sentEmails[0]['message']);
}

public function captureEmail(null|bool $return, array $mailArgs): bool {
$this->sentEmails[] = $mailArgs;
return true;
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

private static function createWpService(): WpInsertPost&GetUserBy&WpUpdatePost&WpSetCurrentUser&DoAction&WpGetPostTerms {
return new NativeWpService();
}
}
