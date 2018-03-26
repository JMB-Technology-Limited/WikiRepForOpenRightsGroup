<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ChangeFieldTypeStringToStringWithLocale
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function change(Field $field, Locale $locale) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $doctrine->persist($field);
        $doctrine->flush($field);

        /** @var RecordHasFieldStringValue $recordHasFieldStringValue */
        foreach($doctrine->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findByField($field) as $recordHasFieldStringValue) {

            $recordHasFieldStringWithLocaleValue = new RecordHasFieldStringWithLocaleValue();
            $recordHasFieldStringWithLocaleValue->setLocale($locale);
            $recordHasFieldStringWithLocaleValue->setValue($recordHasFieldStringValue->getValue());
            $recordHasFieldStringWithLocaleValue->setField($recordHasFieldStringValue->getField());
            $recordHasFieldStringWithLocaleValue->setRecord($recordHasFieldStringValue->getRecord());
            $recordHasFieldStringWithLocaleValue->setCreatedAt($recordHasFieldStringValue->getCreatedAt());
            $recordHasFieldStringWithLocaleValue->setCreationEvent($recordHasFieldStringValue->getCreationEvent());
            $recordHasFieldStringWithLocaleValue->setApprovedAt($recordHasFieldStringValue->getApprovedAt());
            $recordHasFieldStringWithLocaleValue->setApprovalEvent($recordHasFieldStringValue->getApprovalEvent());
            $recordHasFieldStringWithLocaleValue->setRefusedAt($recordHasFieldStringValue->getRefusedAt());
            $recordHasFieldStringWithLocaleValue->setRefusalEvent($recordHasFieldStringValue->getRefusalEvent());

            $doctrine->persist($recordHasFieldStringWithLocaleValue);
            $doctrine->remove($recordHasFieldStringValue);
            $doctrine->flush(array($recordHasFieldStringValue,$recordHasFieldStringWithLocaleValue));

        }

    }

}
