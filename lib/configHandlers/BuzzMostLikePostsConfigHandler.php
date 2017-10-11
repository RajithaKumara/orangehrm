<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 8:59 AM
 */
class BuzzMostLikePostsConfigHandler extends NumericValueConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzMostLikePosts',
            'key' => 'buzz_most_like_posts'
        );
    }

}