<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueMultiSelectEdit extends FieldValueMultiSelect {

    protected $addSelectValues = array();

    protected $removeSelectValues = array();


    public function __construct(FieldValueMultiSelect $fieldValueMultiSelect = null, Field $field = null) {
        if ($fieldValueMultiSelect) {
            $this->publicID = $fieldValueMultiSelect->publicID;
            $this->title = $fieldValueMultiSelect->title;
            $this->selectValues = $fieldValueMultiSelect->getSelectValues();
        } else {
            $this->publicID = $field->getPublicId();
            $this->title = $field->getTitle();
        }
    }

    public function addValueToAdd(SelectValue $selectValue) {
        $this->addSelectValues[] = $selectValue;
    }

    public function addValueToRemove(SelectValue $selectValue) {
        $this->removeSelectValues[] = $selectValue;
    }

    /**
     * @return array
     */
    public function getAddSelectValues()
    {
        return $this->addSelectValues;
    }

    /**
     * @return array
     */
    public function getRemoveSelectValues()
    {
        return $this->removeSelectValues;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }




}
