<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueDateEdit extends FieldValueDate {

    /** @var \DateTime  */
    protected $newValue;

    public function __construct(FieldValueDate $fieldValueDate = null, Field $field = null) {
        if ($fieldValueDate) {
            $this->publicID = $fieldValueDate->publicID;
            $this->title = $fieldValueDate->title;
            $this->value = $fieldValueDate->value;
            $this->newValue = $fieldValueDate->value;
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
