<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldEmailValueType;
use DirectokiBundle\ImportCSVLineResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormError;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeEmail extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'email';
    const FIELD_TYPE_API1 = 'email';

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldEmailValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldEmailValue();
        }

        return $r;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldEmailValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }


    public function getLatestFieldValuesFromCache( Field $field, Record $record ) {
        // TODO: Implement getLatestFieldValuesFromCache() method.
    }

    public function getModerationsNeeded(Field $field, Record $record) {
        return array();
    }

    public function getLabel() {
        return "Email";
    }

    public function isMultipleType() {
        return false;
    }

    public function getEditFieldFormClass( Field $field, Record $record, Locale $locale ) {
        return RecordHasFieldEmailValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return array(
            'current'=>$dataHasField,
        );
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {
        $data = $form->get('value')->getData();
        $value = $this->checkAndProcessValueForExistingRecord($data, $field, $record, $event, $approve, $form->get('value'));
        return $value ? [ $value ] : [];
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/Email/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record, BaseLocaleMode $localeMode , $useCachedData = false) {
        // TODO respect $useCachedData! (Must actually implement  getLatestFieldValuesFromCache first!)
        $latest = $this->getLatestFieldValue($field, $record);
        return array('value'=>$latest->getValue());
    }



    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_value')) {
            $data = $parameterBag->get('field_' . $field->getPublicId() . '_value');
            if ($record) {
                $value = $this->checkAndProcessValueForExistingRecord($data, $field, $record, $event);
            } else {
                $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $event);
            }
            return $value ? [$value] : [];
        }
        return [];
    }


    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve=false) {
        $data = $fieldValueEdit->getNewValue();
        if ($record) {
            $value = $this->checkAndProcessValueForExistingRecord($data, $field, $record, $event, $approve);
        } else {
            $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $event, $approve);
        }
        return $value ? [ $value ] : [];
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false ) {

        $column = intval($fieldConfig['column']);
        $data  = $lineData[$column];

        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published);
        return $value ? new ImportCSVLineResult(
            $data,
            array($value)
        ) : null;

    }


    public function getDataForCache( Field $field, Record $record ) {
        $val = $this->getLatestFieldValue($field, $record);
        return $val ? array('value'=>$val->getValue()) : array();
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), EmailType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function processNewRecordForm(Field $field, Record $record,Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    public function getViewTemplateNewRecordForm() {
        return '@Directoki/FieldType/Email/newRecordForm.html.twig';
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
        return array();
    }

    public function getFullTextSearch(Field $field, Record $record, Locale $locale)
    {
        $value = $this->getLatestFieldValue($field, $record);
        return $value ? $value->getValue() : '';
    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), EmailType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
            'data' => $this->getLatestFieldValue($field, $record)->getValue(),
        ));
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/Email/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForExistingRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $formBuilderInterface->add('field_'.$field->getPublicId(), EmailType::class, array(
            'required' => false,
            'label'=>$field->getTitle(),
        ));
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/Email/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];
    }

    protected function checkAndProcessValueForExistingRecord($newValue, Field $field, Record $record, Event $event, $published = false, $formField = null)
    {
        $newValue = trim($newValue);
        $currentValue = '';
        if ( $record !== null ) {
            $latestValueObject = $this->getLatestFieldValue($field, $record);
            $currentValue = $latestValueObject->getValue();
        }
        if ($newValue != $currentValue) {
            return $this->processValue($newValue, $field, $record, $event, $published, $formField);
        }
        return null;
    }

    protected function checkAndProcessValueForNewRecord($newValue, Field $field, Record $record = null, Event $creationEvent, $published = false, $formField = null)
    {
        $newValue = trim($newValue);
        if ($newValue) {
            return $this->processValue($newValue, $field, $record, $creationEvent, $published, $formField);
        }
        return null;
    }

    protected function processValue($value, Field $field, Record $record = null, Event $event, $published = false, $formField = null)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            if ($formField) {
                $formField->addError(new FormError('Please enter a valid email address'));
            }
            throw new DataValidationException('Please set a valid email!');
        }
        $newRecordHasFieldValues = new RecordHasFieldEmailValue();
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
