<?php

namespace DirectokiBundle\Service;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\FieldType\FieldTypeURL;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeService
{


    protected $container;
    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->fieldTypes[] = new FieldTypeString($container);
        $this->fieldTypes[] = new FieldTypeStringWithLocale($container);
        $this->fieldTypes[] = new FieldTypeText($container);
        $this->fieldTypes[] = new FieldTypeBoolean($container);
        $this->fieldTypes[] = new FieldTypeLatLng($container);
        $this->fieldTypes[] = new FieldTypeEmail($container);
        $this->fieldTypes[] = new FieldTypeURL($container);
        $this->fieldTypes[] = new FieldTypeMultiSelect($container);
        $this->fieldTypes[] = new FieldTypeDate($container);
        $this->fieldTypes[] = new FieldTypeSelect($container);
    }

    protected $fieldTypes = array();

    public function getByField(Field $field) {
        foreach($this->fieldTypes as $fieldType) {
            if($field->getFieldType() == $fieldType::FIELD_TYPE_INTERNAL) {
                return $fieldType;
            }
        }
    }


}

