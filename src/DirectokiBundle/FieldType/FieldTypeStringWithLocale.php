<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Form\Type\RecordHasFieldStringWithLocaleValueType;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\MultiLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldValue;
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
class FieldTypeStringWithLocale extends BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'stringWithLocale';
    const FIELD_TYPE_API1 = 'stringWithLocale';

    public function isMultipleType()
    {
        return true;
    }

    public function getLabel()
    {
        return 'String With Locale';
    }

    protected function getLatestFieldValueForLocale(Field $field, Record $record, Locale $locale) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringWithLocaleValue');

        $r = $repo->findLatestFieldValue($field, $record, $locale);

        if (!$r) {
            $r = new RecordHasFieldStringWithLocaleValue();
            $r->setLocale($locale);
        }

        return $r;

    }

    public function getLatestFieldValues(Field $field, Record $record)
    {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($record->getDirectory()->getProject()) as $locale) {
            $out[] = $this->getLatestFieldValueForLocale($field, $record, $locale);
        }

        return $out;

    }


    public function getDataForCache(Field $field, Record $record)
    {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($record->getDirectory()->getProject()) as $locale) {
            $out[$locale->getId()] = array(
                'locale_public_id'=>$locale->getPublicId(),
                'locale_title'=>$locale->getTitle(),
                'value'=>$this->getLatestFieldValueForLocale($field, $record, $locale)->getValue(),
            );
        }

        return array('data'=>$out);

    }

    public function getLatestFieldValuesFromCache(Field $field, Record $record)
    {

        $out = array();

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()]) && isset($record->getCachedFields()[$field->getId()]['data']) && is_array($record->getCachedFields()[$field->getId()]['data'])) {

            foreach($record->getCachedFields()[$field->getId()]['data'] as $k=>$v) {

                $locale = new Locale();
                $locale->setPublicId($v['locale_public_id']);
                $locale->setTitle($v['locale_title']);

                $newRecordHasFieldValues = new RecordHasFieldStringWithLocaleValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLocale($locale);
                $newRecordHasFieldValues->setValue($v['value'] ? $v['value'] : '');
                $out[] = $newRecordHasFieldValues;

            }

        }

        return $out;
    }

    public function getFieldValuesToModerate(Field $field, Record $record)
    {
        // Nothing here - see getModerationsNeeded()
        return array();
    }

    public function getModerationsNeeded(Field $field, Record $record)
    {
        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringWithLocaleValue');

        foreach ($repo->getFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldValue($fieldValue);
        }

        return $out;

    }

    public function getEditFieldFormClass( Field $field, Record $record , Locale $locale) {
        return RecordHasFieldStringWithLocaleValueType::class;
    }
    public function getEditFieldFormOptions( Field $field, Record $record , Locale $locale) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        $locales = $repo->findByProject($record->getDirectory()->getProject());

        $values = array();
        foreach($locales as $locale) {
            $values[$locale->getPublicId()] = $this->getLatestFieldValueForLocale($field, $record, $locale)->getValue();
        }

        return array(
            'locales'=>$locales,
            'values'=>$values,
        );
    }

    public function getEditFieldFormNewRecords(
        Field $field,
        Record $record,
        Event $event,
        $form,
        User $user = null,
        $approve = false
    ) {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($record->getDirectory()->getProject()) as $locale) {

            $newValue = self::filterValue($form->get('value_'.$locale->getPublicId())->getData());
            $currentValue = self::filterValue($this->getLatestFieldValueForLocale($field, $record, $locale)->getValue());

            if ($newValue != $currentValue) {

                $newRecordHasFieldValues = new RecordHasFieldStringWithLocaleValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLocale($locale);
                $newRecordHasFieldValues->setValue($newValue);
                $newRecordHasFieldValues->setCreationEvent($event);
                if ($approve) {
                    $newRecordHasFieldValues->setApprovedAt(new \DateTime());
                    $newRecordHasFieldValues->setApprovalEvent($event);
                }
                $out[] = $newRecordHasFieldValues;

            }

        }

        return $out;

    }

    public function getViewTemplate()
    {
        return '@Directoki/FieldType/StringWithLocale/view.html.twig';
    }

    public function getAPIJSON(Field $field, Record $record, BaseLocaleMode $localeMode, $useCachedData = false)
    {
        if ($useCachedData) {

            if ($localeMode instanceof SingleLocaleMode) {
                foreach($this->getLatestFieldValuesFromCache($field, $record) as $value) {
                    if ($value->getLocale()->getPublicId() == $localeMode->getLocale()->getPublicId()) {
                        return array(
                            'value'=>$value->getValue(),
                        );
                    }
                }

            } else if ($localeMode instanceof MultiLocaleMode) {

                $out = array();
                foreach($this->getLatestFieldValuesFromCache($field, $record) as $value) {
                    if ($localeMode->containsPublicId($value->getLocale()->getPublicId())) {
                        $out['value_' . $value->getLocale()->getPublicId()] = $value->getValue();
                    }
                }
                return $out;

            }

        } else {

            if ($localeMode instanceof SingleLocaleMode) {

                return array(
                    'value' => $this->getLatestFieldValueForLocale($field, $record, $localeMode->getLocale())->getValue(),
                );

            } else if ($localeMode instanceof MultiLocaleMode) {

                $out = array();
                foreach($localeMode->getLocales() as $locale) {
                    $out['value_'.$locale->getPublicId()] = $this->getLatestFieldValueForLocale($field, $record, $locale)->getValue();
                }
                return $out;

            }

        }

        // Just in case nothing matched; eg For NoLocaleMode on a with Locale Field, we aren't going to try and do anything.
        return array();
    }

    public function processAPI1Record(Field $field, Record $record, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode)
    {
        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($record->getDirectory()->getProject()) as $locale) {

            if ($parameterBag->has('field_'.$field->getPublicId().'_value_'. $locale->getPublicId())) {

                $newValue = self::filterValue($parameterBag->get('field_'.$field->getPublicId().'_value_'. $locale->getPublicId()));
                $currentValue = self::filterValue($this->getLatestFieldValueForLocale($field, $record, $locale)->getValue());

                if ($newValue != $currentValue) {

                    $newRecordHasFieldValues = new RecordHasFieldStringWithLocaleValue();
                    $newRecordHasFieldValues->setRecord($record);
                    $newRecordHasFieldValues->setField($field);
                    $newRecordHasFieldValues->setLocale($locale);
                    $newRecordHasFieldValues->setValue($newValue);
                    $newRecordHasFieldValues->setCreationEvent($event);
                    $out[] = $newRecordHasFieldValues;

                }

            }
        }

        return $out;
    }

    public function processInternalAPI1Record(
        BaseFieldValue $fieldValueEdit,
        Directory $directory,
        Record $record = null,
        Field $field,
        Event $event,
        $approve=false
    ) {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($directory->getProject()) as $locale) {

            if ($fieldValueEdit->getNewValue($locale->getPublicId())) {

                $newValue = self::filterValue($fieldValueEdit->getNewValue($locale->getPublicId()));
                $currentValue = $record ? self::filterValue($this->getLatestFieldValueForLocale($field, $record, $locale)->getValue()) : '';

                if ($newValue != $currentValue) {

                    $newRecordHasFieldValues = new RecordHasFieldStringWithLocaleValue();
                    $newRecordHasFieldValues->setRecord($record);
                    $newRecordHasFieldValues->setField($field);
                    $newRecordHasFieldValues->setLocale($locale);
                    $newRecordHasFieldValues->setValue($newValue);
                    $newRecordHasFieldValues->setCreationEvent($event);
                    if ($approve) {
                        $newRecordHasFieldValues->setApprovalEvent($event);
                    }
                    $out[] = $newRecordHasFieldValues;

                }

            }
        }

        return $out;
    }

    /**
     * @return ImportCSVLineResult|null
     */
    public function parseCSVLineData(
        Field $field,
        $fieldConfig,
        $lineData,
        Record $record,
        Event $creationEvent,
        $published = false
    ) {
        // TODO: Implement parseCSVLineData() method.
    }


    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($field->getDirectory()->getProject()) as $locale) {


            $formBuilderInterface->add('field_'.$field->getPublicId().'_value_'.$locale->getPublicId(), TextType::class, array(
                'required' => false,
                'label' => $field->getTitle(). ' ('.$locale->getTitle().')',
            ));

        }


    }

    public function getViewTemplateNewRecordForm()
    {
        return '@Directoki/FieldType/StringWithLocale/newRecordForm.html.twig';
    }

    public function processNewRecordForm(
        Field $field,
        Record $record,
        Form $form,
        Event $creationEvent,
        Locale $locale,
        $published = false
    ) {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        foreach($repo->findByProject($record->getDirectory()->getProject()) as $locale) {

            $newValue = self::filterValue($form->get('field_'.$field->getPublicId().'_value_'.$locale->getPublicId())->getData());

            if ($newValue) {
                $newRecordHasFieldValues = new RecordHasFieldStringWithLocaleValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLocale($locale);
                $newRecordHasFieldValues->setValue($newValue);
                $newRecordHasFieldValues->setCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setApprovedAt(new \DateTime());
                    $newRecordHasFieldValues->setApprovalEvent($creationEvent);
                }
                $out[] = $newRecordHasFieldValues;
            }

        }

        return $out;


    }

    public function getExportCSVHeaders(Field $field)
    {
        // TODO: Implement getExportCSVHeaders() method.
        return array();
    }

    public function getExportCSVData(Field $field, Record $record)
    {
        // TODO: Implement getExportCSVData() method.
        return array();
    }


    public function getURLsForExternalCheck(Field $field, Record $record)
    {
        // TODO: Implement getURLsForExternalCheck() method.
        return array();
    }

    public function getFullTextSearch(Field $field, Record $record, Locale $locale)
    {
        $value = $this->getLatestFieldValueForLocale($field, $record, $locale);
        return $value ? $value->getValue() : '';
    }

    public function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        // TODO: Implement addToPublicEditRecordForm() method.
    }

    public function getViewTemplatePublicEditRecordForm()
    {
        return '@Directoki/FieldType/StringWithLocale/publicEditRecordForm.html.twig';
    }

    public function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        // TODO: Implement processPublicEditRecordForm() method.
        return array();
    }

    public function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale)
    {
        // TODO: Implement addToPublicNewRecordForm() method.
    }

    public function getViewTemplatePublicNewRecordForm()
    {
        return '@Directoki/FieldType/StringWithLocale/publicNewRecordForm.html.twig';
    }

    public function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published = false)
    {
        // TODO: Implement processPublicNewRecordForm() method.
        return array();
    }


    public static function filterValue($value) {
        return trim(str_replace("\r","", str_replace("\n","", $value)));
    }

}
