<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueStringWithLocaleEdit extends FieldValueStringWithLocale {

    protected $newValues;

    public function __construct(FieldValueStringWithLocale $fieldValueStringWithLocale = null, Field $field = null) {
        if ($fieldValueStringWithLocale) {
            $this->publicID = $fieldValueStringWithLocale->publicID;
            $this->title = $fieldValueStringWithLocale->title;
            $this->values = $fieldValueStringWithLocale->values;
            $this->newValues = $fieldValueStringWithLocale->values;
        } else {
            $this->publicID = $field->getPublicId();
            $this->title = $field->getTitle();
        }
    }

    /**
     * @return mixed
     */
    public function getNewValue($locale) {
        return $this->newValues[$locale];
    }


    public function setNewValue( $locale, $newValue ) {
        $this->newValues[$locale] = $newValue;
    }



}
