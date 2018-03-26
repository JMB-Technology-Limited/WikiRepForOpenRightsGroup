<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueSelectEdit extends FieldValueSelect {

    protected $newValue = null;



    public function __construct(FieldValueSelect $fieldValueSelect = null, Field $field = null) {
        if ($fieldValueSelect) {
            $this->publicID = $fieldValueSelect->publicID;
            $this->title = $fieldValueSelect->title;
            $this->newValue = $fieldValueSelect->getSelectValue();
            $this->selectValue = $fieldValueSelect->getSelectValue();
        } else {
            $this->publicID = $field->getPublicId();
            $this->title = $field->getTitle();
        }
    }

    /**
     * @return SelectValue|null
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param SelectValue|null $newValue
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
    }




    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }




}
