<?php

namespace EventManager\Modules\FrontendForm;

use WpService\Contracts\GetQueryVar;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\UpdatePostMeta;

class FormSecurity
{
    public function __construct(
        private GetQueryVar&GetPostMeta&UpdatePostMeta $wpService, 
        private string $formIdQueryParam, 
        private string $formTokenQueryParam
    ){}

    /**
     * Checks if the request needs a tokenized request.
     * 
     * This method checks if the request needs a tokenized request by checking if the form ID query parameter is set.
     * 
     * @return bool True if the request needs a tokenized request, false otherwise.
     */
    public function needsTokenizedRequest(): bool
    {
        if($this->wpService->getQueryVar($this->formIdQueryParam, false)) {
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
        if(is_null($token)) {
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
     * @return bool True if the form edit token was saved, false otherwise.
     */
    public function saveFormEditToken($postId, $post, $update): bool
    {
        $formEditTokenKey = 'form_edit_token';

        if($this->wpService->getPostMeta($postId, $formEditTokenKey, true) === "") {
            return (bool) $this->wpService->updatePostMeta(
                $postId, 
                $formEditTokenKey, 
                $this->generateFromEditToken($postId)
            );
        }
        return false;
    }

    /**
     * Retrieves the stored form edit token.
     *
     * This method returns the stored form edit token by retrieving the value of the specified query parameter.
     *
     * @param int $postId The post ID.
     * @return string The stored form edit token.
     */
    private function getStoredFromEditToken($postId): string
    {
        return $this->wpService->getPostMeta(
            $postId, 
            'form_edit_token', 
            true
        );
    }
}
