<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueSelect extends BaseFieldValue {

    /** @var SelectValue  */
    protected $selectValue;

    function __construct( $publicID, $title, SelectValue $selectValue = null ) {
        $this->publicID = $publicID;
        $this->title = $title;
        $this->selectValue = $selectValue;
    }

    public function getSelectValue() {
        return $this->selectValue;
    }

}
