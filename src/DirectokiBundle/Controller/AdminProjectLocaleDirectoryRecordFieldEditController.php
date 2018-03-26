<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Exception\DataValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryRecordFieldEditController extends AdminProjectLocaleDirectoryRecordFieldController
{

    protected function build(string $projectId, string $localeId, string $directoryId, string $recordId, string $fieldId) {
        parent::build($projectId, $localeId, $directoryId, $recordId, $fieldId);
        // parent function will do security
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }


    public function editAction(string $projectId, string $localeId, string $directoryId, string $recordId, string $fieldId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId, $fieldId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($this->field);

        $form = $this->createForm($fieldType->getEditFieldFormClass($this->field, $this->record, $this->locale), null, $fieldType->getEditFieldFormOptions($this->field, $this->record, $this->locale));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('createdComment')->getData()
                );

                try {
                    $recordHasFieldValuesToSave = $fieldType->getEditFieldFormNewRecords($this->field, $this->record, $event, $form, $this->getUser(), $form->get('approve')->getData());
                    // There might be nothing to save!
                    if ($recordHasFieldValuesToSave) {
                        $doctrine->persist($event);
                        foreach ($recordHasFieldValuesToSave as $recordHasFieldValueToSave) {
                            $doctrine->persist($recordHasFieldValueToSave);
                        }
                        $doctrine->flush();

                        $updateRecordCacheAction = new UpdateRecordCache($this->container);
                        $updateRecordCacheAction->go($this->record);
                    }

                    return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_record_show', array(
                        'projectId' => $this->project->getPublicId(),
                        'localeId' => $this->locale->getPublicId(),
                        'directoryId' => $this->directory->getPublicId(),
                        'recordId' => $this->record->getPublicId(),
                    )));
                } catch (DataValidationException $dataValidationException) {
                    // Do nothing; the form will display the message fine.
                }
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecordFieldEdit:edit'.$this->field->getFieldType().'.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'field' => $this->field,
            'form' => $form->createView(),
        ));

    }



}
