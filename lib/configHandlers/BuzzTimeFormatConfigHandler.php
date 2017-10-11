<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 10:39 AM
 */
class BuzzTimeFormatConfigHandler extends StandardDateFormatConfigHandler
{

    public function getAttributes() {
        return array(
            'tag' => 'buzzTimeFormat',
            'key' => 'buzz_time_format'
        );
    }
    
}