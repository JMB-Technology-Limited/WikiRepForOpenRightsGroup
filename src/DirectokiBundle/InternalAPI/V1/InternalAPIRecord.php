<?php

namespace DirectokiBundle\InternalAPI\V1;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueDate;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringWithLocale;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\RecordEdit;

use DirectokiBundle\InternalAPI\V1\Model\RecordReportEdit;
use DirectokiBundle\InternalAPI\V1\Model\SelectValue;
use DirectokiBundle\InternalAPI\V1\Result\EditRecordResult;
use DirectokiBundle\InternalAPI\V1\Result\ReportRecordResult;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIRecord
{

    protected $container;

    /** @var  Project */
    protected $project;


    /** @var  Directory */
    protected $directory;


    /** @var \DirectokiBundle\Entity\Record */
    protected $record;

    /** @var  BaseLocaleMode */
    protected $localeMode;

    function __construct($container, Project $project, Directory $directory, \DirectokiBundle\Entity\Record $record, BaseLocaleMode $localeMode)
    {
        $this->container = $container;
        $this->project = $project;
        $this->directory = $directory;
        $this->record = $record;
        $this->localeMode = $localeMode;
    }

    function getPublished()
    {
        $doctrine = $this->container->get('doctrine')->getManager();


        // check published
        $recordHasState = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);
        if ($recordHasState->getState() != RecordHasState::STATE_PUBLISHED) {
            throw new \Exception("Not Found State");
        }

        // Get data, return

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);

        $fieldValues = array();
        foreach($fields as $field) {

            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $tmp = $fieldType->getLatestFieldValues($field, $this->record);

            if ($field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueString($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            } else if ($field->getFieldType() == FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL) {
                $values = array();
                foreach($tmp as $t) {
                    $values[$t->getLocale()->getPublicId()] = $t->getValue();
                }
                $fieldValues[$field->getPublicId()] = new FieldValueStringWithLocale($field->getPublicId(), $field->getTitle(), $values);
            } else if ($field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueText($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            } else if ($field->getFieldType() == FieldTypeEmail::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueEmail($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            } else if ($field->getFieldType() == FieldTypeDate::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueDate($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            } else if ($field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueLatLng($field->getPublicId(), $field->getTitle(), $tmp[0]->getLat(), $tmp[0]->getLng());
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
                if ($tmp[0]->getSelectValue()) {
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

        return new \DirectokiBundle\InternalAPI\V1\Model\Record($this->project->getPublicId(), $this->directory->getPublicId(), $this->record->getPublicId(), $fieldValues);

    }


    function getPublishedEdit()
    {
        if ($this->container->getParameter('directoki.read_only')) {
            throw new \Exception('Directoki is in Read Only mode.');
        }

        return new RecordEdit($this->getPublished());
    }


    function savePublishedEdit(RecordEdit $recordEdit, Request $request = null) {

        if ($this->container->getParameter('directoki.read_only')) {
            throw new \Exception('Directoki is in Read Only mode.');
        }

        $doctrine = $this->container->get('doctrine')->getManager();

        if ($recordEdit->getProjectPublicId() != $this->project->getPublicId()) {
            throw new \Exception('Passed wrong project!');
        }
        if ($recordEdit->getDirectoryPublicId() != $this->directory->getPublicId()) {
            throw new \Exception('Passed wrong Directory!');
        }
        if ($recordEdit->getPublicID() != $this->record->getPublicId()) {
            throw new \Exception('Passed wrong Record!');
        }

        $approve = false;

        if ($recordEdit->isApproveInstantlyIfAllowed() && $recordEdit->getUser()) {
            $projectVoter = $this->container->get('directoki.project_voter');
            if ($projectVoter->getVoteOnProjectForAttributeForUser($this->project, ProjectVoter::ADMIN, $recordEdit->getUser())) {
                $approve = true;
            }
        }

        $event = $this->container->get('directoki_event_builder_service')->build(
            $this->project,
            $recordEdit->getUser(),
            $request,
            $recordEdit->getComment()
        );

        $fieldDataToSave = array();
        $dataValidationErrors = array();
        foreach ( $recordEdit->getFieldValueEdits() as $fieldEdit ) {

            $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldEdit->getPublicID()));

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            try {
                $fieldDataToSave = array_merge(
                    $fieldDataToSave,
                    $fieldType->processInternalAPI1Record($fieldEdit, $this->directory, $this->record, $field, $event, $approve)
                );
            } catch (\DirectokiBundle\Exception\DataValidationException $dataValidationError) {
                $dataValidationErrors[$fieldEdit->getPublicID()] = array(new \DirectokiBundle\InternalAPI\V1\Exception\DataValidationException($dataValidationError->getMessage()));
            }

        }

        if ($dataValidationErrors) {

            return new EditRecordResult(false, false, $dataValidationErrors);

        } else if ($fieldDataToSave) {

            $email = $recordEdit->getEmail();
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->container->get('logger')->error('An edit on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            foreach($fieldDataToSave as $entityToSave) {
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            $action = new UpdateRecordCache($this->container);
            $action->go($this->record);

            return new EditRecordResult(true, $approve);

        } else {
            return new EditRecordResult(false, false);
        }
    }


    function saveReport(RecordReportEdit $recordReportEdit, Request $request = null)
    {

        if ($this->container->getParameter('directoki.read_only')) {
            throw new \Exception('Directoki is in Read Only mode.');
        }

        $doctrine = $this->container->get('doctrine')->getManager();

        if ($recordReportEdit->getDescription()) {

            $event = $this->container->get('directoki_event_builder_service')->build(
                $this->project,
                $recordReportEdit->getUser(),
                $request,
                null
            );
            $email = trim($recordReportEdit->getEmail());
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->container->get('logger')->error('A new report on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            $recordReport = new RecordReport();
            $recordReport->setCreationEvent($event);
            $recordReport->setRecord($this->record);
            $recordReport->setDescription($recordReportEdit->getDescription());
            $doctrine->persist($recordReport);

            $doctrine->flush();

            $action = new UpdateRecordCache($this->container);
            $action->go($this->record);

            return new ReportRecordResult(true);
        } else {
            return new ReportRecordResult(false);
        }

    }

}
