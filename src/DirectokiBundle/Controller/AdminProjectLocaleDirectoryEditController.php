<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Form\Type\FieldNewBooleanType;
use DirectokiBundle\Form\Type\FieldNewDateType;
use DirectokiBundle\Form\Type\FieldNewEmailType;
use DirectokiBundle\Form\Type\FieldNewLatLngType;
use DirectokiBundle\Form\Type\FieldNewMultiSelectType;
use DirectokiBundle\Form\Type\FieldNewSelectType;
use DirectokiBundle\Form\Type\FieldNewStringType;
use DirectokiBundle\Form\Type\FieldNewStringWithLocaleType;
use DirectokiBundle\Form\Type\FieldNewTextType;
use DirectokiBundle\Form\Type\FieldNewURLType;
use DirectokiBundle\Form\Type\RecordNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryEditController extends AdminProjectLocaleDirectoryController
{

    protected function build(string $projectId, string $localeId, string $directoryId) {
        parent::build($projectId, $localeId, $directoryId);
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }

    public function newStringFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);

        $form = $this->createForm(FieldNewStringType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newStringField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newStringWithLocaleFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);

        $form = $this->createForm(FieldNewStringWithLocaleType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newStringWithLocaleField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newEmailFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);

        $form = $this->createForm(FieldNewEmailType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newEmailField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newURLFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeURL::FIELD_TYPE_INTERNAL);

        $form = $this->createForm( FieldNewURLType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newURLField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }



    public function newTextFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);

        $form = $this->createForm(FieldNewTextType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newTextField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }

    public function newBooleanFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeBoolean::FIELD_TYPE_INTERNAL);

        $form = $this->createForm(FieldNewBooleanType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newBooleanField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }

    public function newLatLngFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);

        $form = $this->createForm( FieldNewLatLngType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newLatLngField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }

    public function newMultiSelectFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);

        $form = $this->createForm( FieldNewMultiSelectType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newMultiSelectField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newDateFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);

        $form = $this->createForm( FieldNewDateType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newDateField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newSelectFieldAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $field = new Field();
        $field->setDirectory($this->directory);
        $field->setFieldType(FieldTypeSelect::FIELD_TYPE_INTERNAL);

        $form = $this->createForm( FieldNewSelectType::class, $field);
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

                $field->setSort($doctrine->getRepository('DirectokiBundle:Field')->getNextFieldSortValue($this->directory));
                $field->setCreationEvent($event);
                $doctrine->persist($field);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_fields', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newSelectField.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
        ));


    }


    public function newRecordAction(string $projectId, string $localeId, string $directoryId, Request $request) {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $fields        = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );


        $form = $this->createForm( RecordNewType::class, null, array('container'=>$this->container, 'fields'=>$fields,'locale'=>$this->locale));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $approve = $form->get('approve')->getData();

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('comment')->getData()
                );
                $event->setAPIVersion(1);

                $record = new Record();
                $record->setCachedState($approve ? RecordHasState::STATE_PUBLISHED : RecordHasState::STATE_DRAFT);
                $record->setDirectory($this->directory);
                $record->setCreationEvent($event);

                $doctrine->persist($record);
                $doctrine->persist($event);

                if ($approve) {
                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord($record);
                    $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
                    $recordHasState->setCreationEvent($event);
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                    $doctrine->persist($recordHasState);
                }

                $anyDataValidationErrors = false;
                foreach ($fields as $field) {
                    $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
                    try {
                        foreach ($fieldType->processNewRecordForm($field, $record, $form, $event, $this->locale, $approve) as $entity) {
                            $doctrine->persist($entity);
                        }
                    } catch (DataValidationException $dataValidationException) {
                        $anyDataValidationErrors = true;
                    }
                }

                if (!$anyDataValidationErrors) {
                    $doctrine->flush();

                    if ($approve) {
                        $updateRecordCache = new UpdateRecordCache($this->container);
                        $updateRecordCache->go($record);
                    }

                    return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                        'projectId' => $this->project->getPublicId(),
                        'localeId' => $this->locale->getPublicId(),
                        'directoryId' => $this->directory->getPublicId(),
                        'recordId' => $record->getPublicId(),
                    )));
                }
            }
        }


        $localeRepo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:Locale');

        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryEdit:newRecord.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
            'fields' => $fields,
            'locales' => $localeRepo->findByProject($this->project),
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
        ));

    }

}
