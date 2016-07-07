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

        if(strlen($number) < 5)
            return null;

        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $number = $phoneUtil->parse($number, 'SE');

        return $phoneUtil->format($number, $numberFormat);
    }

    public static function number($number)
    {
        return preg_replace('/\D/', '', $number);
    }

    public static function email($email)
    {
        if(is_null($email) || preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i", $email) != 1)
            return null;
        return $email;
    }

    public static function string($string)
    {
        $count = 0;

        $removedCopy = str_replace(" (copy)", "", trim($string), $count);
        $count = 0;

        // Replace all comment tags so we save them from the strip_tags function
        $changedComments = preg_replace("/<![^>]*>/", '[HTMLCOMMENT]', $removedCopy, -1 , $count);
        if($count > 0)
        {
            // Remove all tags except those passed as argument 2 in strip_tags
            $commentBefore = $changedComments;
            $changedComments = strip_tags($changedComments, '<h1><h2><h3><h4><h5><h6><strong><ul><li><b>');

            // Put back the <!--more--> that we earlier replaced
            $changedComments = str_replace('[HTMLCOMMENT]', "<!--more-->", $changedComments);
        }

        $count = 0;

        $removedShortcodes = preg_replace("/\[.*\]/i", '', $changedComments, -1 , $count);

        return $removedShortcodes;
    }
}
