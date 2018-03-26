<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\URLTools;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldTextValueType;
use DirectokiBundle\ImportCSVLineResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeText extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'text';
    const FIELD_TYPE_API1 = 'text';

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldTextValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldTextValue();
        }

        return $r;

    }

    public function getLatestFieldValuesFromCache(Field $field, Record $record) {
        return array($this->getLatestFieldValueFromCache($field, $record));
    }

    protected  function getLatestFieldValueFromCache(Field $field, Record $record) {

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && isset($record->getCachedFields()[$field->getId()]['value'])) {
            $r = new RecordHasFieldTextValue();
            $r->setValue($record->getCachedFields()[$field->getId()]['value']);
            return $r;
        }

    }


    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldTextValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getModerationsNeeded(Field $field, Record $record) {
        return array();
    }

    public function getLabel() {
        return "Text";
    }


    public function isMultipleType() {
        return false;
    }

    public function getEditFieldFormClass( Field $field, Record $record , Locale $locale) {
        return RecordHasFieldTextValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return array(
            'current'=>$dataHasField,
        );
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        // TODO see if value has changed before saving!! Can return array() if not.


        $newRecordHasFieldValues = new RecordHasFieldTextValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setValue($form->get('value')->getData());
        $newRecordHasFieldValues->setCreationEvent($event);
        if ($approve) {
            $newRecordHasFieldValues->setApprovedAt(new \DateTime());
            $newRecordHasFieldValues->setApprovalEvent($event);
        }

        return array ($newRecordHasFieldValues);
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/Text/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record, BaseLocaleMode $localeMode , $useCachedData = false) {
        $latest = $useCachedData ? $this->getLatestFieldValueFromCache($field, $record) : $this->getLatestFieldValue($field, $record);
        return $latest ? array('value'=>$latest->getValue()) : null;
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_value')) {
            $currentValue = '';
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValue = $latestValueObject->getValue();
            }
            $newValue = FieldTypeText::filterValue($parameterBag->get('field_'.$field->getPublicId().'_value'));
            if ($newValue != $currentValue) {
                $newRecordHasFieldValues = new RecordHasFieldTextValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setValue($newValue);
                $newRecordHasFieldValues->setCreationEvent($event);
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }

    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve=false) {
        if ($fieldValueEdit->getNewValue()) {
            $currentValue = '';
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValue = $latestValueObject->getValue();
            }
            $newValue = FieldTypeText::filterValue($fieldValueEdit->getNewValue());
            if ($newValue != $currentValue) {
                $newRecordHasFieldValues = new RecordHasFieldTextValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setValue($newValue);
                $newRecordHasFieldValues->setCreationEvent($event);
                if ($approve) {
                    $newRecordHasFieldValues->setApprovalEvent($event);
                }
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData ,  Record $record, Event $creationEvent, $published=false) {

        $column = intval($fieldConfig['column']);
        $data  = $lineData[$column];

        if ($data) {
            $newRecordHasFieldValues = new RecordHasFieldTextValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setValue($data);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }

            return new ImportCSVLineResult(
                $data,
                array($newRecordHasFieldValues)
            );
        }

    }

    public function getDataForCache( Field $field, Record $record ) {
        $val = $this->getLatestFieldValue($field, $record);
        return $val ? array('value'=>$val->getValue()) : array();
    }

    public static function filterValue($value) {
        return trim(str_replace("\r","", $value));
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextareaType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {

        $data = $form->get('field_'.$field->getPublicId())->getData();
        if ($data) {
            $newRecordHasFieldValues = new RecordHasFieldTextValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setValue($data);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }
            return array($newRecordHasFieldValues);
        }
        return array();
    }

    public function getViewTemplateNewRecordForm() {
        return '@Directoki/FieldType/Text/newRecordForm.html.twig';
    }

    public function getExportCSVHeaders(Field $field)
    {
        return array($field->getTitle());
    }

    public function getExportCSVData(Field $field, Record $record)
    {
        $value = $this->getLatestFieldValue($field, $record);
        return array( $value->getValue() );
    }


    public function getURLsForExternalCheck(Field $field, Record $record)
    {
        $value = $this->getLatestFieldValue($field, $record);
        if ($value) {
            $tools = new URLTools();
            return $tools->getListOfURLsInText($value->getValue());
        } else {
            return array();
        }
    }

    public function getFullTextSearch(Field $field, Record $record, Locale $locale)
    {
        $value = $this->getLatestFieldValue($field, $record);
        return $value ? $value->getValue() : '';
    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextareaType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
            'data' => $this->getLatestFieldValue($field, $record)->getValue(),
        ));
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/Text/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $this->filterValue($form->get('field_'.$field->getPublicId())->getData());
        if ($data != $this->getLatestFieldValue($field, $record)->getValue()) {
            $newRecordHasFieldValues = new RecordHasFieldTextValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setValue($data);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }
            return array($newRecordHasFieldValues);
        }
        return array();
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextareaType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/Text/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        if ($data) {
            $newRecordHasFieldValues = new RecordHasFieldTextValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setValue($data);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }
            return array($newRecordHasFieldValues);
        }
        return array();
    }

}
