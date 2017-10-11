<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:33 AM
 */
class BuzzPostTextLinesConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzPostTextLines',
            'key' => 'buzz_post_text_lines'
        );
    }

}