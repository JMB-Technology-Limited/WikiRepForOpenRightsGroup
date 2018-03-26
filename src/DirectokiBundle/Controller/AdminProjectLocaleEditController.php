<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Form\Type\DirectoryNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleEditController extends AdminProjectLocaleController
{


    protected function build(string $projectId, string $localeId)
    {
        parent::build($projectId, $localeId);
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }


    public function newDirectoryAction(string $projectId, string $localeId, Request $request)
    {

        // build
        $this->build($projectId, $localeId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $directory = new Directory();
        $directory->setProject($this->project);

        $form = $this->createForm( DirectoryNewType::class, $directory);
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

                $directory->setCreationEvent($event);
                $doctrine->persist($directory);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_directory_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$this->locale->getPublicId(),
                    'directoryId'=>$directory->getPublicId()
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectLocaleEdit:newDirectory.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'form' => $form->createView(),
        ));

    }
}
