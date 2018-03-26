<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Form\Type\PublicRecordReportType;
use DirectokiBundle\Form\Type\PublicRecordEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleDirectoryRecordEditController extends ProjectLocaleDirectoryRecordController
{

    public function reportAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        if (!$this->project->isWebReportAllowed()) {
            throw new NotFoundHttpException('Report Feature Not Found');
        }

        $form = $this->createForm( PublicRecordReportType::class, null, array('user'=>$this->getUser()));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    null
                );
                $doctrine->persist($event);

                if (!$this->getUser()) {
                    $email = trim($form->get('email')->getData());
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $event->setContact($doctrine->getRepository('DirectokiBundle:Contact')->findOrCreateByEmail($this->project, $email));
                    }
                }

                $report = new RecordReport();
                $report->setCreationEvent($event);
                $report->setRecord($this->record);
                $report->setDescription($form->get('description')->getData());
                $doctrine->persist($report);

                $doctrine->flush();

                $action = new UpdateRecordCache($this->container);
                $action->go($this->record);

                $this->addFlash("notice","Your information has been received and will be moderated - thank you!");

                return $this->redirect($this->generateUrl('directoki_project_locale_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }


        return $this->render('DirectokiBundle:ProjectLocaleDirectoryRecordEdit:report.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),

        ));

    }

    public function editAction(string $projectId, string $localeId, string $directoryId, string $recordId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        if (!$this->project->isWebModeratedEditAllowed()) {
            throw new NotFoundHttpException('Edit Feature Not Found');
        }

        $fields = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

        $form = $this->createForm(
            PublicRecordEditType::class,
            null,
            array(
                'user'=>$this->getUser(),
                'record'=>$this->record,
                'container'=>$this->container,
                'fields' => $fields,
                'locale' => $this->locale,
            )
        );
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('comment')->getData()
                );

                $fieldDataToSave = array();
                $anyDataValidationErrors = false;
                foreach ( $fields as $field ) {

                    $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

                    try {
                        $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processPublicEditRecordForm($field, $this->record, $form, $event, $this->locale, false));
                    } catch (DataValidationException $dataValidationError) {
                        $anyDataValidationErrors = true;
                    }
                }

                if (!$anyDataValidationErrors) {
                    if ($fieldDataToSave) {

                        if (!$this->getUser()) {
                            $email = trim($form->get('email')->getData());
                            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $event->setContact($doctrine->getRepository('DirectokiBundle:Contact')->findOrCreateByEmail($this->project, $email));
                            }
                        }

                        $doctrine->persist($event);

                        foreach ($fieldDataToSave as $entityToSave) {
                            $doctrine->persist($entityToSave);
                        }

                        $doctrine->flush();

                        $action = new UpdateRecordCache($this->container);
                        $action->go($this->record);
                    }

                    $this->addFlash("notice", "Your information has been received and will be moderated - thank you!");

                    return $this->redirect($this->generateUrl('directoki_project_locale_directory_record_show', array(
                        'projectId' => $this->project->getPublicId(),
                        'localeId' => $this->locale->getPublicId(),
                        'directoryId' => $this->directory->getPublicId(),
                        'recordId' => $this->record->getPublicId(),
                    )));
                }
            }
        }


        return $this->render('DirectokiBundle:ProjectLocaleDirectoryRecordEdit:edit.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
            'fields' => $fields,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
        ));

    }

}
