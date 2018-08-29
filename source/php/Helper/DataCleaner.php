<?php

namespace HbgEventImporter\Helper;

class DataCleaner
{
    /**
     * Parses a phone number string with libphonenumber
     * @link https://github.com/giggsey/libphonenumber-for-php
     *
     * @param  string $number Phone number to format
     * @return array          Formatted phone number (international and national)
     */
    public static function phoneNumber($number, $numberFormat = \libphonenumber\PhoneNumberFormat::NATIONAL)
    {
        if (is_null($number)) {
            return $number;
        }

        if (strlen($number) < 5)
            return null;

        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $number = $phoneUtil->parse($number, 'SE');

        return $phoneUtil->format($number, $numberFormat);
    }

    /**
     * Removing all non numbers from a number
     * @param  int $number
     * @return int
     */
    public static function number($number)
    {
        return preg_replace('/\D/', '', $number);
    }

    /**
     * Check if $email is an correct edited email
     * @param  string $email
     * @return if ok $email otherwise null
     */
    public static function email($email)
    {
        if (is_null($email) || preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i", $email) != 1)
            return null;
        return $email;
    }

    /**
     * Removing ' (copy)', html tags and shortcodes from a string, keeping the <!--more-->
     * @param  string $string
     * @return string edited $string
     */
    public static function string($string)
    {
        if (!is_string($string)) {
            return $string;
        }

        $string = self::replaceBreaks($string);

        $count = 0;
        $removedCopy = str_replace(" (copy)", "", trim($string), $count);
        $count = 0;

        // Replace all comment tags so we save them from the strip_tags function
        $changedComments = preg_replace("/<![^>]*>/", '[HTMLCOMMENT]', $removedCopy, -1, $count);
        if ($count > 0) {
            // Remove all tags except those passed as argument 2 in strip_tags
            $changedComments = strip_tags($changedComments, '<h1><h2><h3><h4><h5><h6><strong><ul><li><b>');
            // Put back the <!--more--> that we earlier replaced
            $changedComments = str_replace('[HTMLCOMMENT]', "<!--more-->", $changedComments);
        }

        $count = 0;

        $removedShortcodes = preg_replace("/\[.*\]/i", '', $changedComments, -1, $count);
        $string = html_entity_decode($removedShortcodes);

        return $string;
    }

    /**
     * Replace breaks with new line
     * @param string $string String to be sanitized
     * @return string        Sanitized string
     */
    public static function replaceBreaks($string)
    {
        $breaks = array('<br />', '<br>', '<br/>');
        $string = str_ireplace($breaks, "\r\n", $string);

        return $string;
    }

    /**
     * Format prices
     * @param  string $number Phone number to format
     * @return array          Formatted phone number (international and national)
     */
    public static function price($price)
    {
        if (is_null($price)) {
            return $price;
        }

        $price1 = str_replace(',', '.', $price);
        $price2 = preg_replace('/[^0-9.]+/', '', $price1);
        if (!is_numeric($price2)) {
            return null;
        }

        // Format: 1 234,56
        $price = number_format($price2, 2, ',', ' ');
        $price = str_replace(',00', '', $price);

        return $price;
    }

    /**
     *  Get #hashtags from post content and save as taxonomy.
     * @param  int  $post_id event post id
     * @param  post $post    The post object.
     * @param  bool $update  Whether this is an existing post being updated or not.
     */
    public static function hashtags($post_id, $taxonomy)
    {
        $post_content = get_post($post_id);
        $content = $post_content->post_content;
        preg_match_all("/(#[A-Za-zåäöÅÄÖ][-\w_åäöÅÄÖ]+)/", $content, $hashtags, PREG_PATTERN_ORDER);

        if (empty($hashtags[0])) {
            return;
        }

        $termIds = array();
        foreach ($hashtags[0] as $key => $value) {
            $value = str_replace('#', '', $value);
            $value = mb_strtolower($value, 'UTF-8');
            $term = term_exists($value, $taxonomy);
            if ($term == 0 || $term == null) {
                wp_insert_term($value, $taxonomy, array('slug' => $value));
                $termIds[] = $value;
            } else {
                $termIds[] = (int)$term['term_id'];
            }
        }
        wp_set_object_terms($post_id, $termIds, $taxonomy, false);
        return;
    }

}
