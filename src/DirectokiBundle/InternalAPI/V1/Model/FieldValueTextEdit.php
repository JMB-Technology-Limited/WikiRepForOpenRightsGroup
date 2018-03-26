<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueTextEdit extends FieldValueText {

    protected $newValue;

    public function __construct(FieldValueText $fieldValueText = null, Field $field = null) {
        if ($fieldValueText) {
            $this->publicID = $fieldValueText->publicID;
            $this->title = $fieldValueText->title;
            $this->value = $fieldValueText->value;
            $this->newValue = $fieldValueText->value;
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
