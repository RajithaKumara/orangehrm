<?php

/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

/**
 * Description of BuzzTextParserService
 *
 * @author aruna
 */
class BuzzTextParserService {

    protected static $smiles = array(
        '8:)' => 'cool.ico',
        ':\'(' => 'cry.ico',
        'xD' => 'devil.ico',
        '>:)' => 'devil.ico',
        'x(' => 'angry.ico',
        ':((' => 'cry.ico',
        ':*' => 'kiss.ico',
        ':))' => 'laugh.ico',
        ':D' => 'laugh.ico',
        ':-D' => 'laugh.ico',
        ':x' => 'love.ico',
        '(:|' => 'sleepy.ico',
        ':)' => 'smile.ico',
        ':-)' => 'smile.ico',
        ':(' => 'sad.ico',
        ':-(' => 'sad.ico',
        ':O' => 'surprise.ico',
        ':-O' => 'surprise.ico',
        ':P' => 'tongue.ico',
        ':-P' => 'tongue.ico',
        ';)' => 'wink.ico',
        ';-)' => 'wink.ico'
    );

    /**
     * Add emoticons according to the symbols used in the text passed.
     * 
     * @param $text text to be parsed
     * @return $text emoticons inserted text
     */
    public static function parseText($text) {
        if (BuzzTextParserService::isImage($text) === true) {

            return "<img src=\"" . $text . "\" height=\"100px\" >";
        }
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        if (preg_match($reg_exUrl, $text, $url)) {
            $text = preg_replace($reg_exUrl, "<a href=\"{$url[0]}\" target=\"_blank\">{$url[0]}</a> ", $text);
        }
        foreach (self::$smiles as $key => $img) {

            $emoticonPath = '<img src="' .
                    plugin_web_path('orangehrmBuzzPlugin', 'images/emoticons/') . $img .
                    '" height="18" width="18" />';
            $text = str_replace($key, $emoticonPath, $text);
        }
        return str_replace("\n", "<br />", $text);
    }

    public static function isImage($url) {
        $params = array('http' => array(
                'method' => 'HEAD'));
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            return false;  // Problem with url
        }
        $meta = stream_get_meta_data($fp);
// @codeCoverageIgnoreStart
        if ($meta === false) {
            fclose($fp);
            return false;  // Problem reading data from url
        }
        // @codeCoverageIgnoreEnd
        $wrapper_data = $meta["wrapper_data"];
        if (is_array($wrapper_data)) {
            foreach (array_keys($wrapper_data) as $hh) {
                if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") { // strlen("Content-Type: image") == 19 
                    fclose($fp);
                    return true;
                }
            }
        }
        fclose($fp);
        return false;
    }

}
