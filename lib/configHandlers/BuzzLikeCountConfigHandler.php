<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 8:58 AM
 */
class BuzzLikeCountConfigHandler extends SingleLineConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzLikeCount',
            'key' => 'buzz_like_count'
        );
    }

    public function checkValidity($value){
        $validity = false;
        if(is_numeric($value)){
            $validity = true;
        }
        return $validity;
    }

}