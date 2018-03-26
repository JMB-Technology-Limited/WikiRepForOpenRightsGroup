<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueMultiSelect extends BaseFieldValue {

    protected $selectValues;

    function __construct( $publicID, $title, $selectValues ) {
        $this->publicID = $publicID;
        $this->title = $title;
        $this->selectValues = $selectValues;
    }

    public function getSelectValues() {
        return $this->selectValues;
    }

}
