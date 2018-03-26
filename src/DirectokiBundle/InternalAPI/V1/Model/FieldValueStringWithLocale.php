<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueStringWithLocale extends BaseFieldValue {

    protected $values;

    function __construct( $publicID, $title, $values ) {
        $this->publicID = $publicID;
        $this->title = $title;
        $this->values = $values;
    }



    /**
     * @return mixed
     */
    public function getValue($locale) {
        return $this->values[$locale];
    }

    /**
     * @return mixed
     */
    public function hasLocale($locale) {
        return isset($this->values[$locale]);
    }

}
