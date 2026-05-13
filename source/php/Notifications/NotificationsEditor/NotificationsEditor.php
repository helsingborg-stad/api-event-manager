<?php

namespace EventManager\Notifications\NotificationsEditor;

use AcfService\Contracts\AddLocalFieldGroup;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;

class NotificationsEditor implements Hookable
{
    public const NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP = 'notification_new_organization_admin_user';

    public function __construct(private AddLocalFieldGroup $acfService, private AddAction&__ $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', [$this, 'registerFieldGroups']);
    }

    public function registerFieldGroups(): void
    {
        $this->acfService->addLocalFieldGroup([
            'menu_order' => 2,
            'key'        => 'event_notifications',
            'title'      => $this->wpService->__('Notifications', 'api-event-manager'),
            'fields'     => [
                [
                    'key'     => 'field_notifications_description',
                    'label'   => '',
                    'name'    => 'notifications_description',
                    'type'    => 'message',
                    'message' => sprintf($this->wpService->__('Configure notifications sent out by the event manager, %suse markdown%s in the message field to format the content.', 'api-event-manager'), '<strong>', '</strong>'),
                ],
                [
                    'type'       => 'group',
                    'name'       => static::NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP,
                    'key'        => 'field_notification_new_organization_admin_user',
                    'label'      => $this->wpService->__('New organization admin user', 'api-event-manager'),
                    'sub_fields' => [
                        [
                            'key'          => 'field_' . self::NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP . '_subject',
                            'label'        => $this->wpService->__('Subject', 'api-event-manager'),
                            'name'         => 'subject',
                            'type'         => 'text',
                            'instructions' => '',
                            'required'     => 1,
                        ],
                        [
                            'key'          => 'field_' . self::NEW_ORGANIZATION_ADMIN_USER_NOTIFICATION_GROUP . '_message',
                            'label'        => $this->wpService->__('Message', 'api-event-manager'),
                            'name'         => 'message',
                            'type'         => 'textarea',
                            'instructions' => $this->wpService->__('Variables available for use in the message: {username}, {passwordResetUrl}, {loginUrl}', 'api-event-manager'),
                            'required'     => 1,
                        ],
                    ]
                ],
            ],
            'location'   => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'event-manager-settings',
                    ],
                ],
            ],
        ]);
    }
}
