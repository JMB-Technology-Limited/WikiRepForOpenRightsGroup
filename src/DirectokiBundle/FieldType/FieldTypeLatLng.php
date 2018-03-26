<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldLatLngValueType;
use DirectokiBundle\ImportCSVLineResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeLatLng extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'latlng';
    const FIELD_TYPE_API1 = 'latlng';

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldLatLngValue();
        }

        return $r;

    }


    public function getLatestFieldValuesFromCache(Field $field, Record $record) {
        return array($this->getLatestFieldValueFromCache($field, $record));
    }

    protected  function getLatestFieldValueFromCache(Field $field, Record $record) {
        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && isset($record->getCachedFields()[$field->getId()]['lat'])  && isset($record->getCachedFields()[$field->getId()]['lng'])) {
            $r = new RecordHasFieldLatLngValue();
            $r->setLat($record->getCachedFields()[$field->getId()]['lat']);
            $r->setLng($record->getCachedFields()[$field->getId()]['lng']);
            return $r;
        }
    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getModerationsNeeded(Field $field, Record $record) {
        return array();
    }

    public function getLabel() {
        return "LatLng";
    }

    public function isMultipleType() {
        return false;
    }


    public function getEditFieldFormClass( Field $field, Record $record, Locale $locale ) {
        return RecordHasFieldLatLngValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return array(
            'current'=>$dataHasField,
        );
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        // TODO see if value has changed before saving!! Can return array() if not.


        $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setLat($form->get('lat')->getData());
        $newRecordHasFieldValues->setLng($form->get('lng')->getData());
        $newRecordHasFieldValues->setCreationEvent($event);
        if ($approve) {
            $newRecordHasFieldValues->setApprovedAt(new \DateTime());
            $newRecordHasFieldValues->setApprovalEvent($event);
        }

        return array ($newRecordHasFieldValues);
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/LatLng/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record, BaseLocaleMode $localeMode , $useCachedData = false) {
        // TODO respect $useCachedData! (Must actually implement  getLatestFieldValuesFromCache first!)
        $latest = $this->getLatestFieldValue($field, $record);
        return array('lat'=>$latest->getLat(), 'lng'=>$latest->getLng());
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_lat') && $parameterBag->has('field_'.$field->getPublicId().'_lng')) {
            $currentValueLat = null;
            $currentValueLng = null;
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValueLat = $latestValueObject->getLat();
                $currentValueLng = $latestValueObject->getLng();
            }
            $newValueLat = floatval($parameterBag->get('field_'.$field->getPublicId().'_lat'));
            $newValueLng = floatval($parameterBag->get('field_'.$field->getPublicId().'_lng'));
            if ($this->areOldAndNewValuesDifferent($currentValueLat, $currentValueLng, $newValueLat, $newValueLng)) {
                $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLat($newValueLat);
                $newRecordHasFieldValues->setLng($newValueLng);
                $newRecordHasFieldValues->setCreationEvent($event);
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }


    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve=false) {
        if ($fieldValueEdit->getNewLat() && $fieldValueEdit->getNewLat()) {
            $currentValueLat = null;
            $currentValueLng = null;
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValueLat = $latestValueObject->getLat();
                $currentValueLng = $latestValueObject->getLng();
            }
            $newValueLat = $fieldValueEdit->getNewLat();
            $newValueLng = $fieldValueEdit->getNewLng();
            if ($this->areOldAndNewValuesDifferent($currentValueLat, $currentValueLng, $newValueLat, $newValueLng)) {
                $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLat($newValueLat);
                $newRecordHasFieldValues->setLng($newValueLng);
                $newRecordHasFieldValues->setCreationEvent($event);
                if ($approve) {
                    $newRecordHasFieldValues->setApprovalEvent($event);
                }
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }

    protected function areOldAndNewValuesDifferent($oldValueLat, $oldValueLng, $newValueLat, $newValueLng) {
        return round($oldValueLat, 10) != round($newValueLat, 10) || round($oldValueLng, 10) != round($newValueLng, 10);
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false ) {


        if (isset($fieldConfig['column_lat']) && isset($fieldConfig['column_lng'])) {

            $columnLat = intval($fieldConfig['column_lat']);
            $columnLng = intval($fieldConfig['column_lng']);
            if (isset($lineData[$columnLat]) && isset($lineData[$columnLng]) && $lineData[$columnLat] && $lineData[$columnLng]) {
                $dataLat = floatval($lineData[$columnLat]);
                $dataLng = floatval($lineData[$columnLng]);

                $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLat($dataLat);
                $newRecordHasFieldValues->setLng($dataLng);
                $newRecordHasFieldValues->setCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setApprovalEvent($creationEvent);
                }

                return new ImportCSVLineResult(
                    $dataLat . ", " . $dataLng,
                    array($newRecordHasFieldValues)
                );
            }
        }

    }

    public function getDataForCache( Field $field, Record $record ) {
        $val = $this->getLatestFieldValue($field, $record);
        return $val ? array('lat'=>$val->getLat(), 'lng'=>$val->getLng()) : array();
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {

        $formBuilderInterface->add('field_'.$field->getPublicId().'_lat', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lat',
            'scale'=>12,
        ));

        $formBuilderInterface->add('field_'.$field->getPublicId().'_lng', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lng',
            'scale'=>12,
        ));
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $lat = $form->get('field_'.$field->getPublicId().'_lat')->getData();
        $lng = $form->get('field_'.$field->getPublicId().'_lng')->getData();
        if ($lat && $lng) {
            $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setLat($lat);
            $newRecordHasFieldValues->setLng($lng);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }
            return array($newRecordHasFieldValues);
        }
        return array();
    }

    public function getViewTemplateNewRecordForm() {
        return '@Directoki/FieldType/LatLng/newRecordForm.html.twig';
    }


    public function getExportCSVHeaders(Field $field)
    {
        return array(
            $field->getTitle(). " - Lat",
            $field->getTitle(). " - Lng",
        );
    }

    public function getExportCSVData(Field $field, Record $record)
    {
        $value = $this->getLatestFieldValue($field, $record);
        return array( $value->getLat(), $value->getLng() );
    }


    public function getURLsForExternalCheck(Field $field, Record $record)
    {
        return array();
    }

    public function getFullTextSearch(Field $field, Record $record, Locale $locale)
    {
        return '';
    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $latestValue = $this->getLatestFieldValue($field, $record);

        $formBuilderInterface->add('field_'.$field->getPublicId().'_lat', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lat',
            'scale'=>12,
            'data'=>$latestValue->getLat(),
        ));

        $formBuilderInterface->add('field_'.$field->getPublicId().'_lng', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lng',
            'scale'=>12,
            'data'=>$latestValue->getLng(),
        ));
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/LatLng/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $lat = $form->get('field_'.$field->getPublicId().'_lat')->getData();
        $lng = $form->get('field_'.$field->getPublicId().'_lng')->getData();
        $latestValue = $this->getLatestFieldValue($field, $record);
        if ($this->areOldAndNewValuesDifferent($latestValue->getLat(), $latestValue->getLng(), $lat, $lng)) {
            $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setLat($lat);
            $newRecordHasFieldValues->setLng($lng);
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
        $formBuilderInterface->add('field_'.$field->getPublicId().'_lat', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lat',
            'scale'=>12,
        ));

        $formBuilderInterface->add('field_'.$field->getPublicId().'_lng', NumberType::class, array(
            'required' => false,
            'label'=>$field->getTitle().' Lng',
            'scale'=>12,
        ));
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/LatLng/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $lat = $form->get('field_'.$field->getPublicId().'_lat')->getData();
        $lng = $form->get('field_'.$field->getPublicId().'_lng')->getData();
        if ($lat && $lng) {
            $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setLat($lat);
            $newRecordHasFieldValues->setLng($lng);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }
            return array($newRecordHasFieldValues);
        }
        return array();
    }

}
