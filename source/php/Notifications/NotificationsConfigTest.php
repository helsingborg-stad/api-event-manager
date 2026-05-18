<?php

namespace EventManager\Notifications;

use AcfService\Contracts\GetField;
use EventManager\Notifications\MarkdownParser\MarkdownParserInterface;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\AddQueryArg;
use WpService\Contracts\__;
use WpService\Contracts\GetPasswordResetKey;
use WpService\Contracts\NetworkSiteUrl;
use WpService\Contracts\WpLoginUrl;

class NotificationsConfigTest extends TestCase
{
    /**
    * @testdox getNotificationMessageForNewOrganizationAdminUser() builds the password reset URL from networkSiteUrl and addQueryArg
     */
    public function testGetNotificationMessageForNewOrganizationAdminUserBuildsPasswordResetUrlFromNetworkSiteUrl(): void
    {
        $notificationSender = new class implements NotificationSenderInterface {
            public function send(NotificationInterface $notification): void
            {
            }
        };

        $acfService = new class implements GetField {
            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                if (str_ends_with($selector, '_message')) {
                    return 'Set your password here: {passwordResetUrl}';
                }

                return null;
            }
        };

        $markdownParser = new class implements MarkdownParserInterface {
            public function parse(string $markdown): string
            {
                return $markdown;
            }
        };

        $wpService = new class implements AddQueryArg, GetPasswordResetKey, NetworkSiteUrl, WpLoginUrl, __ {
            public array $addQueryArgCalls = [];

            public function addQueryArg(...$args): string
            {
                $this->addQueryArgCalls[] = $args;

                return 'https://example.com/wp-login.php?login=testuser&key=test-reset-key&action=rp';
            }

            public function getPasswordResetKey(\WP_User $user): string|\WP_Error
            {
                return 'test-reset-key';
            }

            public function wpLoginUrl(string $redirect = '', bool $forceReauth = false): string
            {
                return 'https://example.com/wp/wp-login.php';
            }

            public function networkSiteUrl(string $path = '', string|null $scheme = null): string
            {
                return 'https://example.com/wp-login.php';
            }

            public function __(string $text, string $domain = 'default'): string
            {
                return $text;
            }
        };

        $user = new class extends \WP_User {
            public string $user_login = 'testuser';

            public function __construct()
            {
            }
        };

        $notificationsConfig = new NotificationsConfig(
            $notificationSender,
            $acfService,
            $markdownParser,
            $wpService
        );

        $message = $notificationsConfig->getNotificationMessageForNewOrganizationAdminUser($user);

        static::assertCount(1, $wpService->addQueryArgCalls);
        static::assertSame([
            [
                'login'  => 'testuser',
                'key'    => 'test-reset-key',
                'action' => 'rp'
            ],
            'https://example.com/wp-login.php'
        ], $wpService->addQueryArgCalls[0]);
        static::assertSame(
            'Set your password here: https://example.com/wp-login.php?login=testuser&key=test-reset-key&action=rp',
            $message
        );
    }
}
