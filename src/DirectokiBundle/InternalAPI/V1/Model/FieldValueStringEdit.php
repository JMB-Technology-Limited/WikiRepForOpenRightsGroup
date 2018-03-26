<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueStringEdit extends FieldValueString {

    protected $newValue;

    public function __construct(FieldValueString $fieldValueString = null, Field $field = null) {
        if ($fieldValueString) {
            $this->publicID = $fieldValueString->publicID;
            $this->title = $fieldValueString->title;
            $this->value = $fieldValueString->value;
            $this->newValue = $fieldValueString->value;
        } else {
            $this->publicID = $field->getPublicId();
            $this->title = $field->getTitle();
        }
    }
    /**
     * @return mixed
     */
    public function getNewValue() {
        return $this->newValue;
    }

    /**
     * @param mixed $newValue
     */
    public function setNewValue( $newValue ) {
        $this->newValue = $newValue;
    }



}
