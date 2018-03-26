<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\RecordNote;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Form\Type\RecordEditStateType;
use DirectokiBundle\Form\Type\RecordNoteNewType;
use DirectokiBundle\Form\Type\RecordReportNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryRecordEditController extends AdminProjectLocaleDirectoryRecordController
{

    protected function build(string $projectId, string $localeId, string $directoryId, string $recordId) {
        parent::build($projectId, $localeId, $directoryId, $recordId);
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }

    public function moderateAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {

        // build
        $this->build($projectId,  $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);


        // Save???
        if ($request->getMethod() == "POST") {

            $event = $this->get('directoki_event_builder_service')->build(
                $this->project,
                $this->getUser(),
                $request,
                $request->request->get('comment')
            );
            $anythingToSave = false;

            foreach($fields as $field) {
                $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
                foreach($fieldType->getFieldValuesToModerate($field, $this->record) as $fieldValue) {
                    $key = "field_". $field->getPublicId(). "_". $fieldValue->getId();
                    if ($request->request->get($key) == 'approve') {
                        $fieldValue->setApprovedAt(new \DateTime());
                        $fieldValue->setApprovalEvent($event);
                        $doctrine->persist($fieldValue);
                        $anythingToSave = true;

                    } else if ($request->request->get($key) == 'reject') {
                        $fieldValue->setRefusedAt(new \DateTime());
                        $fieldValue->setRefusalEvent($event);
                        $doctrine->persist($fieldValue);
                        $anythingToSave = true;
                    }
                }
                foreach($fieldType->getModerationsNeeded($field, $this->record) as $moderationNeeded) {
                    $key = "field_". $field->getPublicId(). "_". $moderationNeeded->getFieldValue()->getId();
                    if ($request->request->get($key) == 'approve') {
                        $doctrine->persist($moderationNeeded->approve($event));
                        $anythingToSave = true;
                    } else if ($request->request->get($key) == 'reject') {
                        $doctrine->persist($moderationNeeded->reject($event));
                        $anythingToSave = true;
                    }
                }
            }

            $deletionEventMade = false;
            foreach($doctrine->getRepository('DirectokiBundle:RecordHasState')->findUnmoderatedForRecord($this->record) as $recordHasState) {
                $key = "state_".$recordHasState->getId();
                if ($request->request->get($key) == 'approve') {
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                    $doctrine->persist($recordHasState);
                    $anythingToSave = true;

                } else if ($request->request->get($key) == 'reject') {
                    $recordHasState->setRefusedAt(new \DateTime());
                    $recordHasState->setRefusalEvent($event);
                    $doctrine->persist($recordHasState);
                    $anythingToSave = true;
                } else if ($request->request->get($key) == 'reject_and_delete') {
                    $recordHasState->setRefusedAt(new \DateTime());
                    $recordHasState->setRefusalEvent($event);
                    $doctrine->persist($recordHasState);

                    if (!$deletionEventMade) {
                        $recordHasStateDelete = new RecordHasState();
                        $recordHasStateDelete->setRecord($this->record);
                        $recordHasStateDelete->setState(RecordHasState::STATE_DELETED);
                        $recordHasStateDelete->setCreationEvent($event);
                        $recordHasStateDelete->setApprovalEvent($event);
                        $doctrine->persist($recordHasStateDelete);
                        $deletionEventMade = true;
                    }

                    $anythingToSave = true;
                }
            }

            if ($anythingToSave) {
                $doctrine->persist($event);
                $doctrine->flush();
                $updateRecordCacheAction = new UpdateRecordCache($this->container);
                $updateRecordCacheAction->go($this->record);
                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }

        }


        // Load
        $fieldValues = array();
        $fieldValuesCurrent = array();
        $fieldModerationsNeeded = array();
        foreach($fields as $field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $tmp = $fieldType->getLatestFieldValues($field, $this->record);
            $fieldValuesCurrent[$field->getPublicId()] = $fieldType->isMultipleType() ? $tmp : (count($tmp) > 0 ? $tmp[0] : null);
            $fieldValues[$field->getPublicId()] = $fieldType->getFieldValuesToModerate($field, $this->record);
            $fieldModerationsNeeded[$field->getPublicId()] = $fieldType->getModerationsNeeded($field, $this->record);
        }

        $recordHasStates = $doctrine->getRepository('DirectokiBundle:RecordHasState')->findUnmoderatedForRecord($this->record);

        // Render!
        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:moderate.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'fields' => $fields,
            'fieldValuesCurrent' => $fieldValuesCurrent,
            'fieldValues' => $fieldValues,
            'fieldModerationsNeeded' => $fieldModerationsNeeded,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
            'recordHasStates' => $recordHasStates,
            'state' => $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record),
        ));

    }

    public function newNoteAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request) {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();


        $note = new RecordNote();
        $note->setRecord($this->record);
        $note->setCreatedBy($this->getUser());

        $form = $this->createForm( RecordNoteNewType::class, $note);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $doctrine->persist($note);
                $doctrine->flush();
                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }




        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:newNote.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

    public function newReportAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request) {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();


        $report = new RecordReport();
        $report->setRecord($this->record);

        $form = $this->createForm( RecordReportNewType::class, $report);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    null
                );
                $report->setCreationEvent($event);

                $doctrine->persist($event);
                $doctrine->persist($report);
                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }




        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:newReport.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

    public function editStateDraftAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {


        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $currentStateRecord = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);

        if ($currentStateRecord->getState() == RecordHasState::STATE_DRAFT) {
            throw new  NotFoundHttpException('Not found - record is already in this state');
        }

        $form = $this->createForm(RecordEditStateType::class, null, array('current'=>$currentStateRecord));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('createdComment')->getData()
                );
                $doctrine->persist($event);

                $recordHasState = new RecordHasState();
                $recordHasState->setRecord($this->record);
                $recordHasState->setState(RecordHasState::STATE_DRAFT);
                $recordHasState->setCreationEvent($event);
                if ($form->get('approve')->getData()) {
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                }
                $doctrine->persist($recordHasState);

                $doctrine->flush();

                $updateRecordCacheAction = new UpdateRecordCache($this->container);
                $updateRecordCacheAction->go($this->record);

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));

            }

        }

        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:editStateDraft.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

    public function editStatePublishAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {


        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $currentStateRecord = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);

        if ($currentStateRecord->getState() == RecordHasState::STATE_PUBLISHED) {
            throw new  NotFoundHttpException('Not found - record is already in this state');
        }

        $form = $this->createForm(RecordEditStateType::class, null, array('current'=>$currentStateRecord));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('createdComment')->getData()
                );
                $doctrine->persist($event);

                $recordHasState = new RecordHasState();
                $recordHasState->setRecord($this->record);
                $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
                $recordHasState->setCreationEvent($event);
                if ($form->get('approve')->getData()) {
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                }
                $doctrine->persist($recordHasState);

                $doctrine->flush();

                $updateRecordCacheAction = new UpdateRecordCache($this->container);
                $updateRecordCacheAction->go($this->record);

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));

            }

        }

        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:editStatePublish.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }


    public function editStateDeleteAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {


        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $currentStateRecord = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);

        if ($currentStateRecord->getState() == RecordHasState::STATE_DELETED) {
            throw new  NotFoundHttpException('Not found - record is already in this state');
        }

        $form = $this->createForm(RecordEditStateType::class, null, array('current'=>$currentStateRecord));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('createdComment')->getData()
                );
                $doctrine->persist($event);

                $recordHasState = new RecordHasState();
                $recordHasState->setRecord($this->record);
                $recordHasState->setState(RecordHasState::STATE_DELETED);
                $recordHasState->setCreationEvent($event);
                if ($form->get('approve')->getData()) {
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                }
                $doctrine->persist($recordHasState);

                $doctrine->flush();

                $updateRecordCacheAction = new UpdateRecordCache($this->container);
                $updateRecordCacheAction->go($this->record);

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));

            }

        }

        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordEdit:editStateDelete.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

}
