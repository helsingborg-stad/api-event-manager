<?php

namespace EventManager\Helper;

use WpService\Contracts\GetUserBy;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\WpCreateUser;
use WpService\Contracts\WpGeneratePassword;

class CreateUserFromEmail implements CreateUserFromEmailInterface
{
    public function __construct(private GetUserBy&WpGeneratePassword&WpCreateUser&GetUserdata $wpService)
    {
    }

    public function createUserFromEmail(string $email): \WP_User
    {
        $existingUser = $this->wpService->getUserBy('email', $email);

        if ($existingUser !== false) {
            throw new \Exception('A user with this email already exists.');
        }

        $password = $this->wpService->wpGeneratePassword();
        $userId   = $this->wpService->wpCreateUser($email, $password, $email);

        if ($userId instanceof \WP_Error) {
            // Log error or handle it as needed. For now, we'll just skip creating this user.
            throw new \Exception('Error creating user for email ' . $email . ': ' . $userId->get_error_message());
        }

        return $this->wpService->getUserdata($userId);
    }
}
