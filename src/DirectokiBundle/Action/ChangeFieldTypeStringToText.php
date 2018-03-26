<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\FieldType\FieldTypeText;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ChangeFieldTypeStringToText
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function change(Field $field) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
        $doctrine->persist($field);
        $doctrine->flush($field);

        /** @var RecordHasFieldStringValue $recordHasFieldStringValue */
        foreach($doctrine->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findByField($field) as $recordHasFieldStringValue) {

            $recordHasFieldTextValue = new RecordHasFieldTextValue();
            $recordHasFieldTextValue->setValue($recordHasFieldStringValue->getValue());
            $recordHasFieldTextValue->setField($recordHasFieldStringValue->getField());
            $recordHasFieldTextValue->setRecord($recordHasFieldStringValue->getRecord());
            $recordHasFieldTextValue->setCreatedAt($recordHasFieldStringValue->getCreatedAt());
            $recordHasFieldTextValue->setCreationEvent($recordHasFieldStringValue->getCreationEvent());
            $recordHasFieldTextValue->setApprovedAt($recordHasFieldStringValue->getApprovedAt());
            $recordHasFieldTextValue->setApprovalEvent($recordHasFieldStringValue->getApprovalEvent());
            $recordHasFieldTextValue->setRefusedAt($recordHasFieldStringValue->getRefusedAt());
            $recordHasFieldTextValue->setRefusalEvent($recordHasFieldStringValue->getRefusalEvent());

            $doctrine->persist($recordHasFieldTextValue);
            $doctrine->remove($recordHasFieldStringValue);
            $doctrine->flush(array($recordHasFieldStringValue,$recordHasFieldTextValue));

        }

    }

}
