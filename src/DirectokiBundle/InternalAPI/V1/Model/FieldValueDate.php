<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueDate extends BaseFieldValue {

    /** @var  \DateTime */
    protected $value;

    function __construct( $publicID, $title, $value ) {
        $this->publicID = $publicID;
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

}
