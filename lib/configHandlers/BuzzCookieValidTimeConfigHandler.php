<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 8:19 AM
 */
class BuzzCookieValidTimeConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzCookieValidTime',
            'key' => 'buzz_cookie_valid_time'
        );
    }



}