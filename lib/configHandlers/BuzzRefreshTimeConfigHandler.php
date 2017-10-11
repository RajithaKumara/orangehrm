<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:35 AM
 */
class BuzzRefreshTimeConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzRefreshTime',
            'key' => 'buzz_refresh_time'
        );
    }
}