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

    public static function email($email)
    {
        if(is_null($email) || preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i", $email) != 1)
            return null;
        return $email;
    }

    public static function string($string)
    {
        //var_dump($string);
        $count = 0;

        $removedCopy = str_replace(" (copy)", "", trim($string), $count);

        /*if($count > 0)
        {
            echo "Before:\n";
            var_dump($string);
            echo "After:\n";
            var_dump($removedCopy);
            echo "\n";
        }*/

        $count = 0;

        // Replace all comment tags so we save them from the strip_tags function
        $changedComments = preg_replace("/<![^>]*>/", '[HTMLCOMMENT]', $removedCopy, -1 , $count);
        if($count > 0)
        {
            $commentBefore = $changedComments;
            $changedComments = strip_tags($changedComments, '<h1><h2><h3><h4><h5><h6><strong><ul><li><b>');

            if(strlen($commentBefore) != strlen($changedComments))
            {
                echo "Before: \n";
                var_dump($commentBefore);
                echo "After: \n";
                var_dump($changedComments);
                //die();
            }
            /*echo "Nr of comments removed: " . $count . "\n";
            echo "Before: \n";
            var_dump($removedCopy);
            echo "After: \n";
            var_dump($changedComments);*/
            // Put back the <!--more--> that we earlier replaced
            $changedComments = str_replace('[HTMLCOMMENT]', "<!--more-->", $changedComments);
            //var_dump($changedComments);
            //die();
        }

        $count = 0;

        $removedTags = preg_replace("/<([^!].*?)>/", '', $changedComments, -1 , $count);
        /*if($count > 0)
        {
            echo "Nr of tags removed: " . $count . "\n";
            echo "Before: \n";
            var_dump($removedCopy);
            echo "After: \n";
            var_dump($removedTags);
        }*/

        // we can't use wp_strip_all_tags because in some places there are a html comment that should be kept <!--more-->
        //$processedString = wp_strip_all_tags($removedCopy);

        $count = 0;

        $removedShortcodes = preg_replace("/\[.*\]/i", '', $removedTags, -1 , $count);
        /*if($count > 0)
        {
            echo "Nr of shortcodes removed: " . $count . "\n";
            echo "Before: \n";
            var_dump($removedTags);
            echo "After: \n";
            var_dump($removedShortcodes);
        }*/

        return $removedShortcodes;
    }
}
