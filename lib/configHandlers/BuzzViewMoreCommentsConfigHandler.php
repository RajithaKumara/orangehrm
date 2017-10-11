<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:38 AM
 */
class BuzzViewMoreCommentsConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzViewMoreComments',
            'key' => 'buzz_viewmore_comment'
        );
    }

}