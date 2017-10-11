<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:37 AM
 */
class BuzzShareCountConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzShareCount',
            'key' => 'buzz_share_count'
        );
    }


}