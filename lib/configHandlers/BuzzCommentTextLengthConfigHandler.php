<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/8/17
 * Time: 4:36 PM
 */
class BuzzCommentTextLengthConfigHandler extends SingleLineConfigHandler
{
    public function checkValidity($value){
        $validity = false;
        if(is_numeric($value)){
            $validity = true;
        }
        return $validity;
    }

    public function getAttributes() {
        return array(
            'tag' => 'buzzCommentTextLength',
            'key' => 'buzz_comment_text_lenth'
        );
    }

}