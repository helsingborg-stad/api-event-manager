<?php

namespace EventManager\Modules\FrontendForm;

use WpService\Contracts\GetQueryVar;
use WpService\Contracts\GetPost;
use WpService\Contracts\AddAction;
use WpService\Contracts\IsWPError;
use WpService\Contracts\UpdatePost;

class FormSecurity
{
    public function __construct(
        private GetQueryVar&GetPost&AddAction&UpdatePost&IsWPError $wpService,
        private string $formIdQueryParam,
        private string $formTokenQueryParam     
    ) {
        $this->wpService->addAction(
            "acf/submit_form",
            [$this, 'hijackSaveFormRedirect'],
            10,
            3
        );
    }

    /**
     * Checks if the request needs a tokenized request.
     *
     * This method checks if the request needs a tokenized request by checking if the form ID query parameter is set.
     *
     * @return bool True if the request needs a tokenized request, false otherwise.
     */
    public function needsTokenizedRequest(): bool
    {
        if ($this->wpService->getQueryVar($this->formIdQueryParam, false)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the user has tokenized access.
     *
     * This method checks if the user has tokenized access by checking if the form edit token is valid.
     *
     * @return bool True if the user has tokenized access, false otherwise.
     */
    public function hasTokenizedAccess(): bool
    {
        return $this->isValidFormEditToken(
            $this->wpService->getQueryVar($this->formIdQueryParam, null),
            $this->wpService->getQueryVar($this->formTokenQueryParam, null)
        );
    }

    /**
     * Checks if the form edit token is valid.
     *
     * This method checks if the form edit token is valid by comparing the stored token with the provided token.
     *
     * @param int|null $postId The post ID.
     * @param string|null $token The form edit token.
     *
     * @return bool True if the form edit token is valid, false otherwise.
     */
    public function isValidFormEditToken(?int $postId, ?string $token): bool
    {
        if (is_null($token)) {
            return false;
        }
        return $this->getStoredFromEditToken($postId) === $token;
    }

    /**
     * Generates a form edit token.
     *
     * This method generates a form edit token by hashing the post ID, the current time, and a random string.
     *
     * @param int $postId The post ID.
     *
     * @return string The generated form edit token.
     */
    public function generateFromEditToken($postId): string
    {
        return hash_hmac(
            'sha256',
            $postId . microtime(true) . bin2hex(random_bytes(16)),
            (defined('AUTH_KEY') ? AUTH_KEY : '')
        );
    }

    /**
     * Saves the form edit token.
     *
     * This method saves the form edit token by updating the post meta.
     *
     * @param int $postId The post ID.
     * @param mixed $post The post object.
     * @param bool $update Whether this is an existing post being updated.
     *
     * @return bool True if the form edit token was saved, false on failure, null if the token already exists.
     */
    public function saveFormEditToken($postId, $token): ?bool
    {
        if($this->getStoredFromEditToken($postId) === $token) {
            $postUpdateResult = $this->wpService->updatePost(
                $postId, 
                ['post_password' => $token]
            );
            if($this->wpService->isWpError($postUpdateResult)) {
                return false;
            }
            return true;
        }
        return null;
    }

    /**
     * Retrieves the stored form edit token.
     *
     * This method returns the stored form edit token by retrieving the value of the specified query parameter.
     *
     * @param int $postId The post ID.
     * @return string The stored form edit token.
     */
    private function getStoredFromEditToken($postId): ?string
    {
        return $this->wpService->getPost($postId)->post_password ?? null; 
    }

    /**
     * Hijacks the save form redirect.
     * Partially stolen from ACF.
     * https://github.com/AdvancedCustomFields/acf/blob/ac35ec186010b74ade15df9c86c3b4578d3acb36/includes/forms/form-front.php#L562
     *
     * This method hijacks the save form redirect by redirecting the user to the specified URL.
     *
     * @param array $form The form data.
     * @param int $post_id The post ID.
     *
     * @return void
     */
    public function hijackSaveFormRedirect($form, $post_id)
    {
        //Redirect to the specified URL
        if ($return = acf_maybe_get($form, 'return', false)) {
            $token = $this->generateFromEditToken($post_id);

            //Save token, if already exists false is returned
            $savedFormEditToken = $this->saveFormEditToken($post_id, $token);

            if ($savedFormEditToken === true) {
                //Remove %placeholders%
                $return = str_replace('%post_id%', $post_id, $return);
                $return = str_replace('%post_url%', get_permalink($post_id), $return);

                //Add token to url
                $return = add_query_arg(array(
                    $this->formTokenQueryParam => $token
                ), $return);

                // redirect
                wp_redirect($return);
                exit;
            }
        }
    }
}
