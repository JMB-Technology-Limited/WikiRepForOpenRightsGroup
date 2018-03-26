<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\URLTools;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldStringValueType;
use DirectokiBundle\ImportCSVLineResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeString extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'string';
    const FIELD_TYPE_API1 = 'string';

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldStringValue();
        }

        return $r;

    }

    public function getLatestFieldValuesFromCache(Field $field, Record $record) {
        return array($this->getLatestFieldValueFromCache($field, $record));
    }

    protected  function getLatestFieldValueFromCache(Field $field, Record $record) {

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && isset($record->getCachedFields()[$field->getId()]['value'])) {
            $r = new RecordHasFieldStringValue();
            $r->setValue($record->getCachedFields()[$field->getId()]['value']);
            return $r;
        }

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getModerationsNeeded(Field $field, Record $record) {
        return array();
    }

    public function getLabel() {
        return "String";
    }

    public function isMultipleType() {
        return false;
    }

    public function getEditFieldFormClass( Field $field, Record $record, Locale $locale ) {
        return RecordHasFieldStringValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return array(
            'current'=>$dataHasField,
        );
    }


    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {
        $value = $this->checkAndProcessValueForExistingRecord($form->get('value')->getData(), $field, $record, $event, $approve, $form->get('value'));
        return $value ? [ $value ] : [];
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/String/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record, BaseLocaleMode $localeMode, $useCachedData = false ) {
        $latest = $useCachedData ? $this->getLatestFieldValueFromCache($field, $record) : $this->getLatestFieldValue($field, $record);
        return $latest ? array('value'=>$latest->getValue()) : null;
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_value')) {
            if ($record) {
                $value = $this->checkAndProcessValueForExistingRecord($parameterBag->get('field_' . $field->getPublicId() . '_value'), $field, $record, $event);
            } else {
                $value = $this->checkAndProcessValueForNewRecord($parameterBag->get('field_' . $field->getPublicId() . '_value'), $field, $record, $event);
            }
            return $value ? [$value] : [];
        }
        return array();
    }

    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve = false) {
        if ($record) {
            $value = $this->checkAndProcessValueForExistingRecord($fieldValueEdit->getNewValue(), $field, $record, $event, $approve);
        } else {
            $value = $this->checkAndProcessValueForNewRecord($fieldValueEdit->getNewValue(), $field, $record, $event, $approve);
        }
        return $value ? [ $value ] : [];
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false) {

        $column = intval($fieldConfig['column']);
        $data  = self::filterValue($lineData[$column]);

        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published);
        return $value ? new ImportCSVLineResult(
            $data,
            array($value)
        ) : [];
    }

    public function getDataForCache( Field $field, Record $record ) {
        $val = $this->getLatestFieldValue($field, $record);
        return $val ? array('value'=>$val->getValue()) : array();
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    public function getViewTemplateNewRecordForm() {
        return '@Directoki/FieldType/String/newRecordForm.html.twig';
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
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
            'data' => $this->getLatestFieldValue($field, $record)->getValue(),
        ));
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/String/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForExistingRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), TextType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/String/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    public static function filterValue($value) {
        return trim(str_replace("\r","", str_replace("\n","", $value)));
    }

    protected function checkAndProcessValueForExistingRecord($newValue, Field $field, Record $record, Event $event, $published = false, $formField = null)
    {
        $newValue = self::filterValue($newValue);
        $currentValue = '';
        if ( $record !== null ) {
            $latestValueObject = $this->getLatestFieldValue($field, $record);
            $currentValue = self::filterValue($latestValueObject->getValue());
        }
        if ($newValue != $currentValue) {
            return $this->processValue($newValue, $field, $record, $event, $published, $formField);
        }
        return null;
    }

    protected function checkAndProcessValueForNewRecord($newValue, Field $field, Record $record = null, Event $creationEvent, $published = false, $formField = null)
    {
        $newValue = self::filterValue($newValue);
        if ($newValue) {
            return $this->processValue($newValue, $field, $record, $creationEvent, $published, $formField);
        }
        return null;
    }

    protected function processValue($value, Field $field, Record $record = null, Event $event, $published = false, $formField = null)
    {
        $newRecordHasFieldValues = new RecordHasFieldStringValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setValue($value);
        $newRecordHasFieldValues->setCreationEvent($event);
        if ($published) {
            $newRecordHasFieldValues->setApprovalEvent($event);
        }
        return $newRecordHasFieldValues;
    }


}
