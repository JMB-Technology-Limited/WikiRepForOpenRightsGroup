<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueEmailEdit extends FieldValueEmail {

    protected $newValue;

    public function __construct(FieldValueEmail $fieldValueEmail = null, Field $field = null) {
        if ($fieldValueEmail) {
            $this->publicID = $fieldValueEmail->publicID;
            $this->title = $fieldValueEmail->title;
            $this->value = $fieldValueEmail->value;
            $this->newValue = $fieldValueEmail->value;
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
