<?php

/**
 * Created by PhpStorm.
 * User: sameera
 * Date: 9/11/17
 * Time: 8:21 AM
 */
class BuzzImageMaxDimensionConfigHandler extends NumericValueConfigHandler
{
    public function getAttributes() {
        return array(
            'tag' => 'buzzImageMaxDimension',
            'key' => 'buzz_image_max_dimension'
        );
    }
}