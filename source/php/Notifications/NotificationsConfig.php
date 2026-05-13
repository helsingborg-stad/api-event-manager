<?php

namespace EventManager\Notifications;

use AcfService\Contracts\GetField;
use EventManager\Notifications\MarkdownParser\MarkdownParserInterface;
use EventManager\Notifications\NotificationsEditor\NotificationsEditor;
use Parsedown;
use WpService\Contracts\__;
use WpService\Contracts\GetPasswordResetKey;
use WpService\Contracts\NetworkSiteUrl;
use WpService\Contracts\WpLoginUrl;

class NotificationsConfig implements NotificationsConfigInterface
{
    public function __construct(
        private NotificationSenderInterface $notificationSender,
        private GetField $acfService,
        private MarkdownParserInterface $markdownParser,
        private NetworkSiteUrl&GetPasswordResetKey&WpLoginUrl&__ $wpService
    ) {
    }

    public function getNotificationSender(): NotificationSenderInterface
    {
        return $this->notificationSender;
    }

    public function getNotificationSubjectForNewOrganizationAdminUser(): string
    {
        return
            $this->acfService->getField(NotificationsEditor::NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP . '_subject', 'option')
            ?: 'Välkommen som arrangör i Helsingborgs evenemangskalender!';
    }

    public function getNotificationMessageForNewOrganizationAdminUser(\WP_User $user): string
    {
        $key          = $this->wpService->getPasswordResetKey($user);
        $replacements = [
            'username'         => $user->user_login,
            'passwordResetUrl' => $this->wpService->networkSiteUrl('wp-login.php?login=' . rawurlencode($user->user_login) . "&key=$key&action=rp", 'login'),
            'loginUrl'         => $this->wpService->wpLoginUrl()
        ];


        $message =
            $this->acfService->getField(NotificationsEditor::NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP . '_message', 'option')
            ?: $this->wpService->__('You have been added as an administrator for your organization. You can now manage your organization and its events. To set your password, please click the following link: {passwordResetUrl}', 'api-event-manager');
        ;

        $message = self::replaceVariablesInMessage($message, $replacements);

        return $this->markdownParser->parse($message);
    }

    private static function replaceVariablesInMessage(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace("{" . $key . "}", $value, $text);
        }
        return $text;
    }
}
