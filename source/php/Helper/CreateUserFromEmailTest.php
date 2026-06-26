<?php

namespace EventManager\Helper;

use Override;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Implementations\FakeWpService;
use WpService\WpService;

class CreateUserFromEmailTest extends TestCase
{
    /**
     * @testdox throws an exception when trying to create a user with the same email as an existing user
     */
    public function testCreateUserFromEmailThrowsExceptionWhenUserAlreadyExists(): void
    {
        $existingUserEmail   = 'test@example.com';
        $createUserFromEmail = new CreateUserFromEmail(static::createWpServiceWithExistingUser($existingUserEmail));

        try {
            $createUserFromEmail->createUserFromEmail($existingUserEmail);
            $this->fail('Expected exception not thrown.');
        } catch (\Exception $e) {
            $this->assertEquals('A user with this email already exists.', $e->getMessage());
        }
    }

    /**
     * @testdox throws an exception if user creation fails for any reason
     */
    public function testCreateUserFromEmailThrowsExceptionWhenUserCreationFails(): void
    {

        $email               = 'test@example.com';
        $createUserFromEmail = new CreateUserFromEmail(static::createWpServiceWithFailedUserCreation($email));

        try {
            $createUserFromEmail->createUserFromEmail($email);
            $this->fail('Expected exception not thrown.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Error creating user for email', $e->getMessage());
        }
    }

    /**
     * @testdox successfully creates a new user when the email does not exist
     */
    public function testCreateUserFromEmailSuccessfullyCreatesNewUser(): void
    {

        $email               = 'test@example.com';
        $createUserFromEmail = new CreateUserFromEmail(static::createWpServiceWithSuccessfulUserCreation($email));

        $user = $createUserFromEmail->createUserFromEmail($email);

        static::assertInstanceOf(\WP_User::class, $user);
    }

    private static function createWpServiceWithExistingUser(string $email): WpService
    {
        $user = static::createWpUser();

        return new class ($user) extends FakeWpService {
            public function __construct(private \WP_User $user)
            {
            }

            public function getUserBy(string $field, int|string $value): \WP_User|false
            {
                return $this->user;
            }
        };
    }

    private static function createWpServiceWithFailedUserCreation(string $email): WpService
    {
        return new class ($email) extends FakeWpService {
            public function __construct(private string $email)
            {
            }

            public function getUserBy(string $field, int|string $value): \WP_User|false
            {
                return false; // Simulate that the user does not exist
            }

            public function wpGeneratePassword(int $length = 12, bool $specialChars = true, bool $extraSpecialChars = false): string
            {
                return '';
            }

            public function wpCreateUser(string $username, string $password, string $email = ''): int|WP_Error
            {
                return new \WP_Error();
            }
        };
    }

    private static function createWpServiceWithSuccessfulUserCreation(string $email): WpService
    {
        return new class ($email) extends FakeWpService {
            public function __construct(private string $email)
            {
            }

            public function getUserBy(string $field, int|string $value): \WP_User|false
            {
                return false; // Simulate that the user does not exist
            }

            public function wpGeneratePassword(int $length = 12, bool $specialChars = true, bool $extraSpecialChars = false): string
            {
                return '';
            }

            public function wpCreateUser(string $username, string $password, string $email = ''): int|WP_Error
            {
                return 1; // Simulate successful user creation with user ID 1
            }

            public function getUserdata(int $userId): \WP_User|false
            {
                return new \WP_User();
            }
        };
    }

    private static function createWpUser(): \WP_User
    {
        return new \WP_User();
    }
}
