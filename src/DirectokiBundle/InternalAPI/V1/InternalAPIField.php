<?php

namespace DirectokiBundle\InternalAPI\V1;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\RecordEdit;

use DirectokiBundle\InternalAPI\V1\Model\SelectValue;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIField
{

    protected $container;

    /** @var  Project */
    protected $project;


    /** @var  Directory */
    protected $directory;

    /** @var  BaseLocaleMode */
    protected $localeMode;

    /** @var \DirectokiBundle\Entity\Field */
    protected $field;

    function __construct($container, Project $project, Directory $directory, \DirectokiBundle\Entity\Field $field, BaseLocaleMode $localeMode)
    {
        $this->container = $container;
        $this->project = $project;
        $this->directory = $directory;
        $this->field = $field;
        $this->localeMode = $localeMode;
    }


    function getPublishedSelectValues() {

        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL && $this->field->getFieldType() != FieldTypeSelect::FIELD_TYPE_INTERNAL) {
            throw new \Exception('Not a Select Field!');
        }

        $out = array();
        $doctrine = $this->container->get('doctrine')->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        // TODO sort by title ???
        foreach($repo->findByField($this->field, array('id'=>'ASC')) as $selectValue) {
            if ($this->localeMode instanceof SingleLocaleMode) {
                $out[] = new SelectValue($selectValue->getPublicId(), $selectValue->getCachedTitleForLocale($this->localeMode->getLocale()));
            } else {
                // TODO ???????
            }
        }
        return $out;

    }

    function getPublishedSelectValue(string $valueId) {

        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
            throw new \Exception('Not a Select Field!');
        }

        $doctrine = $this->container->get('doctrine')->getManager();
        $selectValueRepo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        $selectValue = $selectValueRepo->findOneBy(array('field'=>$this->field, 'publicId'=>$valueId));
        if (!$selectValue) {
            throw new \Exception('Value not found');
        }
        if ($this->localeMode instanceof SingleLocaleMode) {
            return new SelectValue($selectValue->getPublicId(), $selectValue->getCachedTitleForLocale($this->localeMode->getLocale()));
        } else {
            // TODO ???????
        }

    }

}
