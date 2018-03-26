<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasStringField;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use Symfony\Component\Form\Form;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
abstract class  BaseFieldType {


    const FIELD_TYPE_INTERNAL = 'abstract';
    const FIELD_TYPE_API1 = 'abstract';


    protected $container;

    function __construct( $container ) {
        $this->container = $container;
    }

    public abstract function isMultipleType();

    public abstract function getLabel();

    public abstract function getLatestFieldValues(Field $field, Record $record);

    public abstract function getLatestFieldValuesFromCache(Field $field, Record $record);

    /**
     * @TODO The plan is, the results from this will in future move to getModerationsNeeded() and this method will be removed.
     */
    public abstract function getFieldValuesToModerate(Field $field, Record $record);

    public abstract function getModerationsNeeded(Field $field, Record $record);

    public abstract function getEditFieldFormClass(Field $field, Record $record, Locale $locale);
    public abstract function getEditFieldFormOptions(Field $field, Record $record, Locale $locale);

    public abstract function getEditFieldFormNewRecords(Field $field, Record $record, Event $event, $form, User $user = null, $approve=false);

    public abstract function getViewTemplate();

    public abstract function getAPIJSON(Field $field, Record $record, BaseLocaleMode $localeMode, $useCachedData = false);

    public abstract function processAPI1Record(Field $field, Record $record, ParameterBag $parameterBag, Event $event, BaseLocaleMode $localeMode);

    public abstract function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Field $field, Event $event, $approve=false);

    /**
     * @return ImportCSVLineResult|null
     */
    public abstract function parseCSVLineData(Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false);

    public abstract function getDataForCache(Field $field, Record $record);

    public abstract function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale);

    public abstract function getViewTemplateNewRecordForm();

    public abstract function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published=false );

    public abstract function addToPublicEditRecordForm(Record $record, Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale);

    public abstract function getViewTemplatePublicEditRecordForm();

    public abstract function processPublicEditRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published=false );

    public abstract function addToPublicNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface, Locale $locale);

    public abstract function getViewTemplatePublicNewRecordForm();

    public abstract function processPublicNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, Locale $locale, $published=false );

    public abstract function getExportCSVHeaders(Field $field);

    public abstract function getExportCSVData(Field $field, Record $record);

    public abstract function getURLsForExternalCheck(Field $field, Record $record);

    public abstract function getFullTextSearch(Field $field, Record $record, Locale $locale);

}
