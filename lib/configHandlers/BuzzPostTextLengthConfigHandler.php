<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:32 AM
 */
class BuzzPostTextLengthConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzPostTextLength',
            'key' => 'buzz_post_text_lenth'
        );
    }

}