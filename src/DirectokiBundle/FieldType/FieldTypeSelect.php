<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\RecordHasFieldSelectValue;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldValue;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldSelectValueType;
use DirectokiBundle\ImportCSVLineResult;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeSelect extends  BaseFieldType
{

    const FIELD_TYPE_INTERNAL = 'select';
    const FIELD_TYPE_API1 = 'select';


    public function getSelectValues(Field $field, Locale $locale = null)
    {
        if ($locale) {
            return $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue')->findByFieldSortForLocale($field, $locale);
        } else {
            $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

            $r = $repo->findBy(array('field' => $field), array('createdAt' => 'asc'));

            return $r;
        }

    }

    public function isMultipleType()
    {
        return false;
    }

    public function getLabel()
    {
        return "Select";
    }

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldSelectValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldSelectValue();
        }

        return $r;

    }


    public function getLatestFieldValuesFromCache(Field $field, Record $record) {
        return array($this->getLatestFieldValueFromCache($field, $record));
    }

    protected  function getLatestFieldValueFromCache(Field $field, Record $record) {

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && isset($record->getCachedFields()[$field->getId()]['value'])) {

            $r = new RecordHasFieldSelectValue();

            $data = $record->getCachedFields()[$field->getId()]['value'];

            if (isset($data['publicId'])) {

                $selectValue = new SelectValue();
                $selectValue->setCachedTitles($data['cachedTitles']);
                // TODO $selectValue->setTitle($data['title']);
                $selectValue->setPublicId($data['publicId']);

                $r->setSelectValue($selectValue);

            }
            return $r;
        }

    }

    public function getFieldValuesToModerate(Field $field, Record $record)
    {
        return array();
    }

    public function getModerationsNeeded(Field $field, Record $record)
    {
        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldSelectValue');
        foreach ($repo->getFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldValue($fieldValue);
        }

        return $out;
    }

    public function getEditFieldFormClass(Field $field, Record $record, Locale $locale)
    {

        return RecordHasFieldSelectValueType::class;
    }

    public function getEditFieldFormOptions(Field $field, Record $record, Locale $locale)
    {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return array(
            'current'=>$dataHasField,
            'container'=>$this->container,
            'field'=>$field,
            'record'=>$record,
            'locale'=>$locale,
        );
    }

    public function getEditFieldFormNewRecords(Field $field, Record $record, Event $event, $form, User $user = null, $approve = false)
    {
        $value = $this->checkAndProcessValueForExistingRecord($form->get('value')->getData(), $field, $record, $event, $approve, $form->get('value'));
        return $value ? [ $value ] : [];
    }

    public function getViewTemplate()
    {
        return '@Directoki/FieldType/Select/view.html.twig';
    }

    public function getAPIJSON(Field $field, Record $record, BaseLocaleMode $localeMode, $useCachedData = false)
    {
        // TODO respect $useCachedData! (Must actually implement  getLatestFieldValuesFromCache first!)

        /** @var RecordHasFieldSelectValue $value */
        $value = $this->getLatestFieldValue($field, $record);
        if ($value && $value->getSelectValue()) {
            return array(
                'value' => array(
                    'id' => $value->getSelectValue()->getPublicId(),
                    'title' => ( $localeMode instanceof SingleLocaleMode ? $value->getSelectValue()->getCachedTitleForLocale($localeMode->getLocale()) : ''),
                )
            );
        } else {
            return array('value' => null);
        }
    }

    public function processAPI1Record(Field $field, Record $record, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode)
    {
        if ($parameterBag->has('field_' . $field->getPublicId() . '_title')) {
            $newValue = $parameterBag->get('field_' . $field->getPublicId() . '_title');
            return $this->processAPI1RecordSetStringValue($newValue, $field, $record, $event, false, $localeMode);
        }
        if ($parameterBag->has('field_' . $field->getPublicId() . '_id')) {
            $newValue = $parameterBag->get('field_' . $field->getPublicId() . '_id');
            return $this->processAPI1RecordSetPublicIdValue($newValue, $field, $record, $event);
        }
        if ($parameterBag->has('field_' . $field->getPublicId() . '_null') && $parameterBag->get('field_' . $field->getPublicId() . '_null')) {
            return $this->processAPI1RecordSetNullValue($field, $record, $event);
        }
        return [];
    }

    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve = false)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        $selectValue = null;
        if ($fieldValueEdit->getNewValue()) {
            $selectValue = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>$fieldValueEdit->getNewValue()->getId()));
        }

        $value = null;
        if ($record) {
            $value = $this->checkAndProcessValueForExistingRecord($selectValue, $field, $record, $event, $approve);
        } else {
            $value = $this->checkAndProcessValueForNewRecord($selectValue, $field, $record, $event, $approve);
        }
        return $value ? [ $value ] : [];

    }

    /**
     * @return ImportCSVLineResult|null
     */
    public function parseCSVLineData(Field $field, $fieldConfig, $lineData, Record $record, Event $creationEvent, $published = false)
    {

        $entitesToSave = array();
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoLocale = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        $locale = null;
        if (isset($fieldConfig['locale'])) {
            $locale = $repoLocale->findOneBy(array('publicId' => $fieldConfig['locale'], 'project' => $field->getDirectory()->getProject()));
        }
        // TODO pass in a localemode object here that is set in global import mode - take from there if not set specifically


        if (isset($fieldConfig['add_title_column']) && $locale) {
            $valueTitle = trim($lineData[$fieldConfig['add_title_column']]);
            if ($valueTitle) {
                $valueObject = $repoSelectValue->findByTitleFromUser($field, $valueTitle, $locale);
                if (!$valueObject) {
                    $valueObject = new SelectValue();
                    $valueObject->setCreationEvent($creationEvent);
                    $valueObject->setField($field);
                    $entitesToSave[] = $valueObject;

                    $valueObjectHasTitle = new SelectValueHasTitle();
                    $valueObjectHasTitle->setTitle($valueTitle);
                    $valueObjectHasTitle->setSelectValue($valueObject);
                    $valueObjectHasTitle->setLocale($locale);
                    $valueObjectHasTitle->setCreationEvent($creationEvent);
                    $entitesToSave[] = $valueObjectHasTitle;

                }
                $newRecordHasFieldValues = new RecordHasFieldSelectValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setSelectValue($valueObject);
                $newRecordHasFieldValues->setCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setApprovalEvent($creationEvent);
                }
                $entitesToSave[] = $newRecordHasFieldValues;
            }
        }

        if ($entitesToSave) {
            $debugOutput = '';
            foreach($entitesToSave as $record) {

                if ($record instanceof SelectValueHasTitle) {
                    // It's a new select value!
                    $debugOutput = "New Select Value: ". $record->getTitle();
                } else if ($record instanceof RecordHasFieldSelectValue && $record->getSelectValue()->getId()) {
                    // It's an existing select value!
                    $debugOutput = $record->getSelectValue()->getCachedTitleForLocale($locale);
                }
            }
            return new ImportCSVLineResult($debugOutput, $entitesToSave);
        }

    }

    public function getDataForCache(Field $field, Record $record)
    {
        $out = array('value'=>array());
        $recordHasFieldMultiSelectValue = $this->getLatestFieldValue($field, $record);
        if ($recordHasFieldMultiSelectValue->getSelectValue()) {
            $out['value'] = array(
                'publicId' => $recordHasFieldMultiSelectValue->getSelectValue()->getPublicId(),
                'cachedTitles' => $recordHasFieldMultiSelectValue->getSelectvalue()->getCachedTitles(),
                // TODO 'title'=>$recordHasFieldMultiSelectValue->getSelectValue()->getTitle(),
            );
        }
        return $out;
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $choices = array();
        foreach($this->getSelectValues($field, $locale) as $selectValue) {
            $choices[$selectValue->getCachedTitleForLocale($locale)] = $selectValue;
        }

        $formBuilderInterface->add('field_'.$field->getPublicId(), ChoiceType::class, array(
            'required' => false,
            'choices' => $choices,
            'label'=> $field->getTitle(),
        ));
    }

    public function getViewTemplateNewRecordForm()
    {
        return '@Directoki/FieldType/Select/newRecordForm.html.twig';
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $data = $form->get('field_'.$field->getPublicId())->getData();
        $value = $this->checkAndProcessValueForNewRecord($data, $field, $record, $creationEvent, $published, $form->get('field_'.$field->getPublicId()));
        return $value ? [ $value ] : [];    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $choices = array();
        foreach($this->getSelectValues($field, $locale) as $selectValue) {
            $choices[$selectValue->getCachedTitleForLocale($locale)] = $selectValue;
        }

        $latest = $this->getLatestFieldValue($field, $record);

        $formBuilderInterface->add('field_'.$field->getPublicId(), ChoiceType::class, array(
            'required' => false,
            'choices' => $choices,
            'data' => $latest->getSelectValue(),
            'label'=> $field->getTitle(),
        ));
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/Select/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $value = $form->get('field_'.$field->getPublicId())->getData();

        if ($value) {
            return $this->processAPI1RecordSetSelectValue($value, $field, $record, $creationEvent , $published);
        }
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $choices = array();
        foreach($this->getSelectValues($field, $locale) as $selectValue) {
            $choices[$selectValue->getCachedTitleForLocale($locale)] = $selectValue;
        }

        $formBuilderInterface->add('field_'.$field->getPublicId(), ChoiceType::class, array(
            'required' => false,
            'choices' => $choices,
            'label'=> $field->getTitle(),
        ));
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/Select/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $value = $form->get('field_'.$field->getPublicId())->getData();

        if ($value) {
            return $this->processAPI1RecordSetSelectValue($value, $field, $record, $creationEvent , $published);
        }
    }

    public function getExportCSVHeaders(Field $field)
    {
        return array( $field->getTitle() );
    }

    public function getExportCSVData(Field $field, Record $record)
    {

        // TODO pass locale, don't just pick one at random
        $repoLocale = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');
        $locale = $repoLocale->findOneBy(['project'=>$record->getDirectory()->getProject()]);

        $latest = $this->getLatestFieldValue($field, $record);
        if ($latest && $latest->getSelectValue()) {
            return  array($latest->getSelectValue()->getCachedTitleForLocale($locale));
        }
        return array();
    }

    public function getURLsForExternalCheck(Field $field, Record $record)
    {
        return array();
    }

    public function getFullTextSearch(Field $field, Record $record, Locale $locale)
    {
        $latest = $this->getLatestFieldValue($field, $record);
        if ($latest && $latest->getSelectValue()) {
            return $latest->getSelectValue()->getCachedTitleForLocale($locale);
        }
        return '';
    }

    protected function checkAndProcessValueForNewRecord(SelectValue $newValue = null, Field $field, Record $record = null, Event $creationEvent, $published = false, $formField = null)
    {
        if ($newValue) {
            return $this->processValue($newValue, $field, $record, $creationEvent, $published, $formField);
        }
        return null;
    }

    protected function checkAndProcessValueForExistingRecord(SelectValue $selectValue = null, Field $field, Record $record, Event $creationEvent, $published = false, $formField = null)
    {

        $repoRecordHasFieldSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldSelectValue');

        $latestValue = $this->getLatestFieldValue($field, $record);
        if ($selectValue && $latestValue->getSelectValue() && $latestValue->getSelectValue()->getId() == $selectValue->getId()) {
            // Value is already set!
            return array();
        }
        if (is_null($selectValue) && is_null($latestValue->getSelectValue())) {
            // NULL is already set!
            return array();
        }

        if ($record && $repoRecordHasFieldSelectValue->doesRecordHaveFieldHaveValueAwaitingModeration($record, $field, $selectValue)) {
            // check someone else has not already tried to add value!
            return array();
        }

        return $this->processValue($selectValue, $field, $record, $creationEvent, $published, $formField);

    }

    protected function processValue(SelectValue $value = null, Field $field, Record $record = null, Event $event, $published = false, $formField = null)
    {
        $newRecordHasFieldValues = new RecordHasFieldSelectValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setSelectValue($value);
        $newRecordHasFieldValues->setCreationEvent($event);
        if ($published) {
            $newRecordHasFieldValues->setApprovalEvent($event);
        }
        return $newRecordHasFieldValues;
    }

    protected function processAPI1RecordSetStringValue($value, Field $field, Record $record = null, Event $event, $approve = false, BaseLocaleMode $localeMode = null)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        if ($localeMode instanceof SingleLocaleMode) {
            $valueObject = $repoSelectValue->findByTitleFromUser($field, $value, $localeMode->getLocale());

            if (!$valueObject) {
                return array(); // TODO We can't find the value the user passed.
            }

            return $this->processAPI1RecordSetSelectValue($valueObject, $field, $record, $event, $approve);

        } else {
            return array(); // TODO
        }

    }

    protected function processAPI1RecordSetPublicIdValue($publicId, Field $field, Record $record = null, Event $event, $approve = false)
    {

        if (!trim($publicId)) {
            return array();
        }

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        $valueObject = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>trim($publicId)));

        if (!$valueObject) {
            return array(); // TODO We can't find the value the user passed.
        }

        return $this->processAPI1RecordSetSelectValue($valueObject, $field, $record, $event, $approve);

    }

    protected function processAPI1RecordSetNullValue(Field $field, Record $record = null, Event $event, $approve = false)
    {
        return $this->processAPI1RecordSetSelectValue(null, $field, $record, $event, $approve);
    }

    protected function processAPI1RecordSetSelectValue(SelectValue $selectValue = null, Field $field, Record $record = null, Event $event, $approve = false)
    {

        $repoRecordHasFieldSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldSelectValue');

        if ($record && $record->getId()) {
            $latestValue = $this->getLatestFieldValue($field, $record);
            if ($selectValue && $latestValue->getSelectValue() && $latestValue->getSelectValue()->getId() == $selectValue->getId()) {
                // Value is already set!
                return array();
            }
            if (is_null($selectValue) && is_null($latestValue->getSelectValue())) {
                // NULL is already set!
                return array();
            }

        }

        if ($record && $repoRecordHasFieldSelectValue->doesRecordHaveFieldHaveValueAwaitingModeration($record, $field, $selectValue)) {
            // check someone else has not already tried to add value!
            return array();
        }

        $newRecordHasFieldValues = new RecordHasFieldSelectValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setSelectValue($selectValue);
        $newRecordHasFieldValues->setCreationEvent($event);
        if ($approve) {
            $newRecordHasFieldValues->setApprovalEvent($event);
        }
        return array($newRecordHasFieldValues);

    }



}
