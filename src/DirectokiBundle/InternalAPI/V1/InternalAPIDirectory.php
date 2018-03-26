<?php

namespace DirectokiBundle\InternalAPI\V1;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;

use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueDate;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueDateEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmailEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLngEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelectEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueSelectEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringWithLocale;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringWithLocaleEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueTextEdit;
use DirectokiBundle\InternalAPI\V1\Model\Record;
use DirectokiBundle\InternalAPI\V1\Model\RecordCreate;
use DirectokiBundle\InternalAPI\V1\Model\SelectValue;
use DirectokiBundle\InternalAPI\V1\Query\RecordsInDirectoryQuery;
use DirectokiBundle\InternalAPI\V1\Result\CreateRecordResult;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIDirectory
{

    protected $container;

    /** @var  Project */
    protected $project;


    /** @var  Directory */
    protected $directory;

    /** @var  BaseLocaleMode */
    protected $localeMode;


    function __construct($container, Project $project, Directory $directory, BaseLocaleMode $localeMode)
    {
        $this->container = $container;
        $this->project = $project;
        $this->directory = $directory;
        $this->localeMode = $localeMode;
    }

    /**
     * @param $recordId
     * @return InternalAPIRecord
     * @throws \Exception
     */
    function getRecordAPI( string $recordId ) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $record = $doctrine->getRepository('DirectokiBundle:Record')->findOneBy(array('directory'=>$this->directory, 'publicId'=>$recordId));
        if (!$record) {
            throw new \Exception("Not Found Record");
        }

        return new InternalAPIRecord($this->container, $this->project, $this->directory, $record, $this->localeMode);
    }


    /**
     * @param $fieldId
     * @return InternalAPIField
     * @throws \Exception
     */
    function getFieldAPI( string $fieldId ) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldId));
        if (!$field) {
            throw new \Exception("Not Found Field");
        }

        return new InternalAPIField($this->container, $this->project, $this->directory, $field, $this->localeMode);
    }


    function getPublishedRecords(RecordsInDirectoryQuery $recordsInDirectoryQuery=null) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $locale = null;
        if ($recordsInDirectoryQuery && $recordsInDirectoryQuery->getLocale()) {
            $locale = $doctrine->getRepository('DirectokiBundle:Locale')->findOneBy(array('project'=>$this->project, 'publicId'=>$recordsInDirectoryQuery->getLocale()->getId()));
        }

        $internalRecordsInDirectoryQuery = new \DirectokiBundle\RecordsInDirectoryQuery(
            $this->directory,
            $locale
        );
        $internalRecordsInDirectoryQuery->setPublishedOnly(true);
        if ($recordsInDirectoryQuery) {
            $internalRecordsInDirectoryQuery->setFullTextSearch($recordsInDirectoryQuery->getFullTextSearch());
        }


        // Get data, return
        $out = array();
        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);

        foreach($doctrine->getRepository('DirectokiBundle:Record')->findByRecordsInDirectoryQuery($internalRecordsInDirectoryQuery) as $record) {

            $fieldValues = array();
            foreach($fields as $field) {
                $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );
                $tmp       = $fieldType->getLatestFieldValuesFromCache( $field, $record );

                if ( $field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueString( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                } else if ($field->getFieldType() == FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL && $tmp) {
                    $values = array();
                    foreach($tmp as $t) {
                        $values[$t->getLocale()->getPublicId()] = $t->getValue();
                    }
                    $fieldValues[$field->getPublicId()] = new FieldValueStringWithLocale($field->getPublicId(), $field->getTitle(), $values);
                } else if ( $field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueText( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                } else if ( $field->getFieldType() == FieldTypeEmail::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueEmail( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                } else if ( $field->getFieldType() == FieldTypeDate::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueDate( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                } else if ( $field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueLatLng( $field->getPublicId(), $field->getTitle(), $tmp[0]->getLat(), $tmp[0]->getLng()  );
                } else if ($field->getFieldType() == FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
                    $selectValues = array();
                    foreach ($tmp as $t) {
                        if ($this->localeMode instanceof SingleLocaleMode) {
                            $selectValues[] = new SelectValue($t->getSelectValue()->getPublicId(), $t->getSelectValue()->getCachedTitleForLocale($this->localeMode->getLocale()));
                        } else {
                            // TODO ?????????
                            $selectValues[] = new SelectValue($t->getSelectValue()->getPublicId(), '?');
                        }
                    }
                    $fieldValues[$field->getPublicId()] = new FieldValueMultiSelect($field->getPublicId(), $field->getTitle(), $selectValues);
                } else if ($field->getFieldType() == FieldTypeSelect::FIELD_TYPE_INTERNAL) {
                    $selectValue = null;
                    if ($tmp[0] && $tmp[0]->getSelectValue()) {
                        if ($this->localeMode instanceof SingleLocaleMode) {
                            $selectValue = new SelectValue($tmp[0]->getSelectValue()->getPublicId(), $tmp[0]->getSelectValue()->getCachedTitleForLocale($this->localeMode->getLocale()));
                        } else {
                            // TODO ?????????
                            $selectValue = new SelectValue($tmp[0]->getSelectValue()->getPublicId(), '?');
                        }
                    }
                    $fieldValues[$field->getPublicId()] = new FieldValueSelect($field->getPublicId(), $field->getTitle(), $selectValue);
                }
            }
            $out[] = new Record($this->project->getPublicId(), $this->directory->getPublicId(), $record->getPublicId(), $fieldValues);
        }

        return $out;

    }

    function getRecordCreate() {

        if ($this->container->getParameter('directoki.read_only')) {
            throw new \Exception('Directoki is in Read Only mode.');
        }

        $doctrine = $this->container->get('doctrine')->getManager();

        $fields = array();
        foreach($doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory) as $field) {

            if ($field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueStringEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueStringWithLocaleEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueTextEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeEmail::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueEmailEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueLatLngEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeDate::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueDateEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueMultiSelectEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeSelect::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueSelectEdit(null, $field);
            }
        }

        return new RecordCreate($this->project->getPublicId(), $this->directory->getPublicId(), $fields);
    }

    function saveRecordCreate(RecordCreate $recordCreate, Request $request = null)
    {

        if ($this->container->getParameter('directoki.read_only')) {
            throw new \Exception('Directoki is in Read Only mode.');
        }

        $doctrine = $this->container->get('doctrine')->getManager();

        if ($recordCreate->getProjectPublicId() != $this->project->getPublicId()) {
            throw new \Exception('Passed wrong project!');
        }
        if ($recordCreate->getDirectoryPublicId() != $this->directory->getPublicId()) {
            throw new \Exception('Passed wrong Directory!');
        }


        $event = $this->container->get('directoki_event_builder_service')->build(
            $this->project,
            $recordCreate->getUser(),
            $request,
            $recordCreate->getComment()
        );

        $approve = false;

        if ($recordCreate->isApproveInstantlyIfAllowed() && $recordCreate->getUser()) {
            $projectVoter = $this->container->get('directoki.project_voter');
            if ($projectVoter->getVoteOnProjectForAttributeForUser($this->project, ProjectVoter::ADMIN, $recordCreate->getUser())) {
                $approve = true;
            }
        }

        $fieldDataToSave = array();
        $dataValidationErrors = array();
        foreach ( $recordCreate->getFieldValueEdits() as $fieldEdit ) {

            $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldEdit->getPublicID()));

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            try {
                $fieldDataToSave = array_merge(
                    $fieldDataToSave,
                    $fieldType->processInternalAPI1Record($fieldEdit, $this->directory, null, $field, $event, $approve)
                );
            } catch (\DirectokiBundle\Exception\DataValidationException $dataValidationError) {
                $dataValidationErrors[$fieldEdit->getPublicID()] = array(new \DirectokiBundle\InternalAPI\V1\Exception\DataValidationException($dataValidationError->getMessage()));
            }

        }

        if ($dataValidationErrors) {

            return new CreateRecordResult(false, false, null, $dataValidationErrors);

        } else if ($fieldDataToSave) {

            $email = $recordCreate->getEmail();
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->get('logger')->error('An edit on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' new record had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            $record = new \DirectokiBundle\Entity\Record();
            $record->setDirectory($this->directory);
            $record->setCreationEvent($event);
            $record->setCachedState($approve ? RecordHasState::STATE_PUBLISHED : RecordHasState::STATE_DRAFT);
            $doctrine->persist($record);

            $recordHasState = new RecordHasState();
            $recordHasState->setRecord($record);
            $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
            $recordHasState->setCreationEvent($event);
            if ($approve) {
                $recordHasState->setApprovalEvent($event);
            }
            $doctrine->persist($recordHasState);

            foreach($fieldDataToSave as $entityToSave) {
                $entityToSave->setRecord($record);
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            $action = new UpdateRecordCache($this->container);
            $action->go($record);

            return new CreateRecordResult(true, $approve, $record->getPublicId());

        } else {
            return new CreateRecordResult(false, false, null);
        }

    }



}
