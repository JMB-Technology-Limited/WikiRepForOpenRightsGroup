<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\Form\Type\PublicRecordNewType;
use DirectokiBundle\RecordsInDirectoryQuery;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleDirectoryEditController extends ProjectLocaleDirectoryController
{


    public function newAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        if (!$this->project->isWebModeratedEditAllowed()) {
            throw new NotFoundHttpException('Edit Feature Not Found');
        }

        $fields = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

        $form = $this->createForm(
            PublicRecordNewType::class,
            null,
            array(
                'user'=>$this->getUser(),
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

                $record = new Record();
                $record->setDirectory($this->directory);
                $record->setCreationEvent($event);
                $record->setCachedState(RecordHasState::STATE_DRAFT);

                $fieldDataToSave = array();
                $anyDataValidationErrors = false;
                foreach ( $fields as $field ) {

                    $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

                    try {
                        $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processPublicNewRecordForm($field, $record, $form, $event, $this->locale, false));
                    } catch (DataValidationException $e) {
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
                        $doctrine->persist($record);

                        // Also record a request to publish this record but don't approve it - moderator will do that.
                        $recordHasState = new RecordHasState();
                        $recordHasState->setRecord($record);
                        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
                        $recordHasState->setCreationEvent($event);
                        $doctrine->persist($recordHasState);

                        foreach ($fieldDataToSave as $entityToSave) {
                            $doctrine->persist($entityToSave);
                        }

                        $doctrine->flush();

                        $action = new UpdateRecordCache($this->container);
                        $action->go($record);

                    }

                    $this->addFlash("notice", "Your information has been received and will be moderated - thank you!");

                    return $this->redirect($this->generateUrl('directoki_project_locale_directory_show', array(
                        'projectId' => $this->project->getPublicId(),
                        'localeId' => $this->locale->getPublicId(),
                        'directoryId' => $this->directory->getPublicId(),
                    )));
                }
            }
        }


        return $this->render('DirectokiBundle:ProjectLocaleDirectoryEdit:new.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'form' => $form->createView(),
            'fields' => $fields,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
        ));

    }


}
