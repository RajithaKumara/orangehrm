<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 8:54 AM
 */
class BuzzInitialCommentsConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzInitialComments',
            'key' => 'buzz_initial_comments'
        );
    }


}