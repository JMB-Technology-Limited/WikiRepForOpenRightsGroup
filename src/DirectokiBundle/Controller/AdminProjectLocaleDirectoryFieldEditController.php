<?php

namespace DirectokiBundle\Controller;


use DirectokiBundle\Action\UpdateSelectValueCache;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\Form\Type\SelectValueNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryFieldEditController extends AdminProjectLocaleDirectoryFieldController {

    protected function build( string $projectId, string $localeId, string $directoryId, string $fieldId ) {
        parent::build( $projectId, $localeId, $directoryId, $fieldId );
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }


    public function newSelectValueAction(string $projectId, string $localeId, string $directoryId, string $fieldId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $fieldId);
        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL && $this->field->getFieldType() != FieldTypeSelect::FIELD_TYPE_INTERNAL) {
            throw new  NotFoundHttpException('Not found');
        }

        //data

        $doctrine = $this->getDoctrine()->getManager();

        $locales = $doctrine->getRepository('DirectokiBundle:Locale')->findByProject($this->project, array('title'=>'ASC'));

        $selectValue = new SelectValue();
        $selectValue->setField($this->field);

        $form = $this->createForm( SelectValueNewType::class, $selectValue, ['locales'=>$locales]);
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

                $selectValue->setCreationEvent($event);
                $doctrine->persist($selectValue);

                foreach($locales as $locale) {
                    $title = trim($form->get('title_'.$locale->getId())->getData());
                    if ($title) {
                        $selectValueHasTitle = new SelectValueHasTitle();
                        $selectValueHasTitle->setSelectValue($selectValue);
                        $selectValueHasTitle->setLocale($locale);
                        $selectValueHasTitle->setTitle($title);
                        $selectValueHasTitle->setCreationEvent($event);
                        $doctrine->persist($selectValueHasTitle);
                    }
                }

                $doctrine->flush();

                $action = new UpdateSelectValueCache($this->container);
                $action->go($selectValue);

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_field_select_values_list', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'fieldId'=>$this->field->getPublicId(),
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryFieldEdit:newSelectValue.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'field' => $this->field,
            'form' => $form->createView(),
        ));


    }



}