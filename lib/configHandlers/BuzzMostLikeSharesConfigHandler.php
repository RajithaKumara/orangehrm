<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:21 AM
 */
class BuzzMostLikeSharesConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzMostLikeShares',
            'key' => 'buzz_most_like_shares'
        );
    }
}