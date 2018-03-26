<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use Symfony\Component\Form\Form;
use DirectokiBundle\ImportCSVLineResult;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldMultiSelectValueType;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueAddition;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueRemoval;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeMultiSelect extends  BaseFieldType
{

    const FIELD_TYPE_INTERNAL = 'multiselect';
    const FIELD_TYPE_API1 = 'multiselect';


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

    public function getLatestFieldValues(Field $field, Record $record)
    {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $r = $repo->findLatestFieldValues($field, $record);

        return $r;

    }


    public function getLatestFieldValuesFromCache( Field $field, Record $record ) {

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && is_array($record->getCachedFields()[$field->getId()]['value'])) {

            $out = array();

            foreach($record->getCachedFields()[$field->getId()]['value'] as $data) {
                $selectValue = new SelectValue();
                $selectValue->setCachedTitles($data['cachedTitles']);
                // TODO $selectValue->setTitle($data['title']);
                $selectValue->setPublicId($data['publicId']);

                $recordHasFieldMultiSelectValue = new RecordHasFieldMultiSelectValue();
                $recordHasFieldMultiSelectValue->setSelectValue($selectValue);

                $out[] = $recordHasFieldMultiSelectValue;
            }

            return $out;
        }
        return array();

    }

    public function getFieldValuesToModerate(Field $field, Record $record)
    {
        return array();
    }


    public function getModerationsNeeded(Field $field, Record $record)
    {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        foreach ($repo->getAdditionFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldMultiValueAddition($fieldValue);
        }
        foreach ($repo->getRemovalFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldMultiValueRemoval($fieldValue);
        }
        return $out;
    }

    public function getLabel()
    {
        return "Multi Select";
    }

    public function isMultipleType()
    {
        return true;
    }

    public function getEditFieldFormClass( Field $field, Record $record , Locale $locale) {
        return RecordHasFieldMultiSelectValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        return array(
            'container'=>$this->container,
            'field'=>$field,
            'record'=>$record,
            'locale'=>$locale,
        );
    }


    public function getEditFieldFormNewRecords(Field $field, Record $record, Event $event, $form, User $user = null, $approve = false)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $out = array();
        foreach ($repoSelectValue->findBy(array('field' => $field)) as $selectValue) {

            if ($form->get('value_' . $selectValue->getPublicId())->getData()) {

                // User has selected this value! Check it's not there already, and add it!

                if (!$repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($record, $field, $selectValue)) {

                    $newRecordHasMultiSelectValues = new RecordHasFieldMultiSelectValue();
                    $newRecordHasMultiSelectValues->setRecord($record);
                    $newRecordHasMultiSelectValues->setField($field);
                    $newRecordHasMultiSelectValues->setSelectValue($selectValue);
                    $newRecordHasMultiSelectValues->setAdditionCreationEvent($event);
                    if ($approve) {
                        $newRecordHasMultiSelectValues->setAdditionApprovedAt(new \DateTime());
                        $newRecordHasMultiSelectValues->setAdditionApprovalEvent($event);
                    }
                    $out[] = $newRecordHasMultiSelectValues;

                }

            } else {

                $recordHasMultiSelectValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $selectValue);

                if ($recordHasMultiSelectValue) {

                    $recordHasMultiSelectValue->setRemovalCreationEvent($event);
                    $recordHasMultiSelectValue->setRemovalCreatedAt(new \DateTime());
                    if ($approve) {
                        $recordHasMultiSelectValue->setRemovalApprovedAt(new \DateTime());
                        $recordHasMultiSelectValue->setRemovalApprovalEvent($event);
                    }
                    $out[] = $recordHasMultiSelectValue;

                }

            }

        }

        return $out;
    }

    public function getViewTemplate()
    {
        return '@Directoki/FieldType/MultiSelect/view.html.twig';
    }

    public function getAPIJSON(Field $field, Record $record, BaseLocaleMode $localeMode, $useCachedData = false)
    {
        // TODO respect $useCachedData! (Must actually implement  getLatestFieldValuesFromCache first!)
        $out = array();
        foreach ($this->getLatestFieldValues($field, $record) as $value) {
            $out[] = array(
                'value' => array(
                    'id' => $value->getSelectValue()->getPublicId(),
                    'title' => ( $localeMode instanceof SingleLocaleMode ? $value->getSelectValue()->getCachedTitleForLocale($localeMode->getLocale()) : ''),
                )
            );
        }
        return array('values' => $out);
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode)
    {
        $out = array();
        if ($parameterBag->has('field_' . $field->getPublicId() . '_add_title')) {
            $newValue = $parameterBag->get('field_' . $field->getPublicId() . '_add_title');
            if (is_array($newValue)) {
                foreach ($newValue as $nv) {
                    $out = array_merge($out, $this->processAPI1RecordAddStringValue($nv, $field, $record, $event, false, $localeMode));
                }
            } else {
                $out = array_merge($out, $this->processAPI1RecordAddStringValue($newValue, $field, $record, $event, false, $localeMode));
            }
        }
        if ($parameterBag->has('field_' . $field->getPublicId() . '_add_id')) {
            $newValue = $parameterBag->get('field_' . $field->getPublicId() . '_add_id');
            if (!is_array($newValue) && strpos($newValue,',') !== false) {
                $newValue = explode(",", $newValue);
            }
            if (is_array($newValue)) {
                foreach ($newValue as $nv) {
                    $out = array_merge($out, $this->processAPI1RecordAddPublicIdValue($nv, $field, $record, $event));
                }
            } else {
                $out = array_merge($out, $this->processAPI1RecordAddPublicIdValue($newValue, $field, $record, $event));
            }
        }
        if ($parameterBag->has('field_' . $field->getPublicId() . '_remove_id')) {
            $removeIdValue = $parameterBag->get('field_' . $field->getPublicId() . '_remove_id');
            if (!is_array($removeIdValue) && strpos($removeIdValue,',') !== false) {
                $removeIdValue = explode(",", $removeIdValue);
            }
            if (is_array($removeIdValue)) {
                foreach ($removeIdValue as $riv) {
                    $out = array_merge($out, $this->processAPI1RecordRemoveStringId($riv, $field, $record, $event));
                }
            } else {
                $out = array_merge($out, $this->processAPI1RecordRemoveStringId($removeIdValue, $field, $record, $event));
            }
        }
        return $out;
    }

    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve=false) {
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $out = array();

        foreach($fieldValueEdit->getAddSelectValues() as $selectValueInternalAPI) {
            $selectValue = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>$selectValueInternalAPI->getId()));
            if ($selectValue) {
                $out = array_merge($out, $this->processAPI1RecordAddSelectValue($selectValue, $field, $record, $event, $approve));
            } else {
                throw new \Exception('Passed a select value we could not find!');
            }
        }

        foreach($fieldValueEdit->getRemoveSelectValues() as $selectValueInternalAPI) {
            $selectValue = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>$selectValueInternalAPI->getId()));
            if ($selectValue) {
                $out = array_merge($out, $this->processAPI1RecordRemoveSelectValue($selectValue, $field, $record, $event, $approve));
            } else {
                throw new \Exception('Passed a select value we could not find!');
            }        }

        return $out;
    }

    protected function processAPI1RecordAddStringValue($value, Field $field, Record $record = null, Event $event, $approve = false, BaseLocaleMode $localeMode = null)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        if ($localeMode instanceof SingleLocaleMode) {
            $valueObject = $repoSelectValue->findByTitleFromUser($field, $value, $localeMode->getLocale());

            if (!$valueObject) {
                return array(); // TODO We can't find the value the user passed.
            }

            return $this->processAPI1RecordAddSelectValue($valueObject, $field, $record, $event, $approve);

        } else {
            return array(); // TODO
        }

    }

    protected function processAPI1RecordAddPublicIdValue($publicId, Field $field, Record $record = null, Event $event, $approve = false)
    {

        if (!trim($publicId)) {
            return array();
        }

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        $valueObject = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>trim($publicId)));

        if (!$valueObject) {
            return array(); // TODO We can't find the value the user passed.
        }

        return $this->processAPI1RecordAddSelectValue($valueObject, $field, $record, $event, $approve);

    }

    protected function processAPI1RecordAddSelectValue(SelectValue $selectValue, Field $field, Record $record = null, Event $event, $approve = false)
    {

        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        if ($record && $repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($record, $field, $selectValue)) {
            // Value is already set!
            return array();
        }

        if ($record && $repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValueAwaitingModeration($record, $field, $selectValue)) {
            // check someone else has not already tried to add value!
            return array();
        }

        $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setSelectValue($selectValue);
        $newRecordHasFieldValues->setAdditionCreationEvent($event);
        if ($approve) {
            $newRecordHasFieldValues->setAdditionApprovalEvent($event);
        }
        return array($newRecordHasFieldValues);

    }


    protected function processAPI1RecordRemoveStringId($value, Field $field, Record $record = null, Event $event, $approve = false)
    {

        if (!trim($value)) {
            return array();
        }

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        $valueObject = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>trim($value)));

        if (!$valueObject) {
            return array(); // TODO We can't find the value the user passed.
        }

        return $this->processAPI1RecordRemoveSelectValue($valueObject, $field, $record, $event, $approve);

    }


    protected function processAPI1RecordRemoveSelectValue(SelectValue $selectValue, Field $field, Record $record = null, Event $event, $approve = false)
    {
        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $recordFieldHasValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $selectValue);

        if (!$recordFieldHasValue) {
            return array(); // TODO Value is not currently set!
        }

        if ($recordFieldHasValue->getRemovalCreationEvent()) {
            return array(); // TODO Someone else has already tried to remove value!
        }

        $recordFieldHasValue->setRemovalCreationEvent($event);
        $recordFieldHasValue->setRemovalCreatedAt(new \DateTime());
        if ($approve) {
            $recordFieldHasValue->setRemovalApprovalEvent($event);
        }
        return array($recordFieldHasValue);
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false ) {

        $entitesToSave = array();
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoLocale = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        $locale = null;
        if (isset($fieldConfig['locale'])) {
            $locale = $repoLocale->findOneBy(array('publicId' => $fieldConfig['locale'], 'project' => $field->getDirectory()->getProject()));
        }
        // TODO pass in a localemode object here that is set in global import mode - take from there if not set specifically

        if (isset($fieldConfig['add_value_id'])) {
            foreach(explode(",", $fieldConfig['add_value_id']) as $valuePublicId) {
                if (trim($valuePublicId)) {
                    $valueObject = $repoSelectValue->findOneBy(array('field' => $field, 'publicId' => trim($valuePublicId)));
                    if ($valueObject) {
                        $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                        $newRecordHasFieldValues->setRecord($record);
                        $newRecordHasFieldValues->setField($field);
                        $newRecordHasFieldValues->setSelectValue($valueObject);
                        $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                        if ($published) {
                            $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                        }
                        $entitesToSave[] = $newRecordHasFieldValues;
                    }
                }
            }
        }

        if (isset($fieldConfig['add_title_column']) && $locale) {
            foreach (explode(",", $lineData[$fieldConfig['add_title_column']]) as $valueTitle) {
                $valueTitle = trim($valueTitle);
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
                    $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                    $newRecordHasFieldValues->setRecord($record);
                    $newRecordHasFieldValues->setField($field);
                    $newRecordHasFieldValues->setSelectValue($valueObject);
                    $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                    if ($published) {
                        $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                    }
                    $entitesToSave[] = $newRecordHasFieldValues;
                }
            }
        }

        if ($entitesToSave) {
            $debugOutput = array();
            foreach($entitesToSave as $record) {

                if ($record instanceof SelectValueHasTitle) {
                    // It's a new select value!
                    $debugOutput[] = "New Select Value: ". $record->getTitle();
                } else if ($record instanceof RecordHasFieldMultiSelectValue && $record->getSelectValue()->getId()) {
                    // It's an existing select value!
                    $debugOutput[] = $record->getSelectValue()->getCachedTitleForLocale($locale);
                }
            }
            return new ImportCSVLineResult(
                implode(', ', $debugOutput),
                $entitesToSave
            );
        }

    }

    public function getDataForCache( Field $field, Record $record ) {
        $out = array('value'=>array());
        foreach($this->getLatestFieldValues($field, $record) as $recordHasFieldMultiSelectValue) {
            $out['value'][] = array(
                'publicId'=>$recordHasFieldMultiSelectValue->getSelectValue()->getPublicId(),
                'cachedTitles'=>$recordHasFieldMultiSelectValue->getSelectvalue()->getCachedTitles(),
                // TODO 'title'=>$recordHasFieldMultiSelectValue->getSelectValue()->getTitle(),
            );
        }
        return $out;
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        foreach ($this->getSelectValues($field) as $selectValue) {
            $formBuilderInterface->add('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label'=> $selectValue->getCachedTitleForLocale($locale),
            ));
        }
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $entitesToSave = array();
        foreach ($this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue')->findByField($field) as $selectValue) {
            $value = $form->get('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId())->getData();
            if ($value) {
                $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setSelectValue($selectValue);
                $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                }
                $entitesToSave[] = $newRecordHasFieldValues;

            }
        }
        return $entitesToSave;
    }

    public function getViewTemplateNewRecordForm()
    {
        return '@Directoki/FieldType/MultiSelect/newRecordForm.html.twig';
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

        $out = array();
        foreach($this->getLatestFieldValues($field, $record) as $latestFieldValue) {
            $out[] = $latestFieldValue->getSelectValue()->getCachedTitleForLocale($locale);
        }
        return array( implode(", ", $out) );
    }


    public function getURLsForExternalCheck(Field $field, Record $record)
    {
        return array();
    }


    public function getFullTextSearch(Field $field, Record $record, \DirectokiBundle\Entity\Locale $locale)
    {
        $out = array();
        foreach ($this->getLatestFieldValues($field, $record) as $record) {
            $out[] = $record->getSelectValue()->getCachedTitleForLocale($locale);
        }
        return implode(' ', $out);
    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');
        foreach ($this->getSelectValues($field, $locale) as $selectValue) {
            $recordFieldHasValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $selectValue);
            $formBuilderInterface->add('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label' => $selectValue->getCachedTitleForLocale($locale),
                'data' => $recordFieldHasValue ? true : false,
            ));
        }
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/MultiSelect/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');
        $entitesToSave = array();
        foreach ($this->getSelectValues($field) as $selectValue) {
            $recordFieldHasValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $selectValue);
            $value = $form->get('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId())->getData();
            if ($value && !$recordFieldHasValue) {
                $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setSelectValue($selectValue);
                $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                }
                $entitesToSave[] = $newRecordHasFieldValues;
            } else if ($recordFieldHasValue && !$value) {
                if ($published) {
                    // TODO!!!!!!!!!!!!!!!!!!!!!!
                } else {
                    if (!$recordFieldHasValue->getRemovalCreationEvent()) {
                        $recordFieldHasValue->setRemovalCreationEvent($creationEvent);
                        $entitesToSave[] = $recordFieldHasValue;
                    }
                }
            }
        }
        return $entitesToSave;
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        foreach ($this->getSelectValues($field, $locale) as $selectValue) {
            $formBuilderInterface->add('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label'=> $selectValue->getCachedTitleForLocale($locale),
            ));
        }
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/MultiSelect/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        $entitesToSave = array();
        foreach ($this->getSelectValues($field) as $selectValue) {
            $value = $form->get('field_'.$field->getPublicId().'_value_'. $selectValue->getPublicId())->getData();
            if ($value) {
                $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setSelectValue($selectValue);
                $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                }
                $entitesToSave[] = $newRecordHasFieldValues;
            }
        }
        return $entitesToSave;
    }

}

