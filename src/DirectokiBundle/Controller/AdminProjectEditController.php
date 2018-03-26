<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\ProjectAdmin;
use DirectokiBundle\Form\Type\AddAdminType;
use DirectokiBundle\Form\Type\DirectoryNewType;
use DirectokiBundle\Form\Type\LocaleNewType;
use DirectokiBundle\Form\Type\ProjectSettingsEditType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectEditController extends AdminProjectController
{



    protected function build(string $projectId) {
        parent::build($projectId);
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }



    public function newLocaleAction(string $projectId, Request $request)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $locale = new Locale();
        $locale->setProject($this->project);

        $form = $this->createForm( LocaleNewType::class, $locale);
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

                $locale->setCreationEvent($event);
                $doctrine->persist($locale);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_locale_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'localeId'=>$locale->getPublicId(),
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectEdit:newLocale.html.twig', array(
            'project' => $this->project,
            'form' => $form->createView(),
        ));

    }

    public function editSettingsAction(string  $projectId, Request $request)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();

        $form = $this->createForm( ProjectSettingsEditType::class, $this->project);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $doctrine->persist($this->project);
                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_settings', array(
                    'projectId'=>$this->project->getPublicId(),
                )));
            }
        }

        return $this->render('DirectokiBundle:AdminProjectEdit:editSettings.html.twig', array(
            'project' => $this->project,
            'form' => $form->createView(),
        ));

    }

    public function addAdminAction(string  $projectId, Request $request)
    {

        // build
        $this->build($projectId);
        if ($this->project->getOwner() != $this->getUser()) {
            throw new HttpException(403, 'Only Owners Can Do That.');
        }

        //data

        $doctrine = $this->getDoctrine()->getManager();
        $userRepository = $doctrine->getRepository('JMBTechnologyUserAccountsBundle:User');
        $projectAdminRepository = $doctrine->getRepository('DirectokiBundle:ProjectAdmin');

        $form = $this->createForm( AddAdminType::class );
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {


                $user = $userRepository->findOneByEmail($form->get('email')->getData());
                if ($user) {

                    if ($projectAdminRepository->findOneBy(array('user'=>$user,'project'=>$this->project))) {
                        $this->addFlash(
                            'error',
                            'This user is already an admin!'
                        );

                    } else {

                        $projectAdmin = new ProjectAdmin();
                        $projectAdmin->setProject($this->project);
                        $projectAdmin->setUser($user);
                        $doctrine->persist($projectAdmin);
                        $doctrine->flush();

                        return $this->redirect($this->generateUrl('directoki_admin_project_user_list', array(
                            'projectId'=>$this->project->getPublicId(),
                        )));

                    }

                } else {
                    $this->addFlash(
                        'error',
                        'We can not find that email address!'
                    );
                }

            }
        }

        return $this->render('DirectokiBundle:AdminProjectEdit:addAdmin.html.twig', array(
            'project' => $this->project,
            'form' => $form->createView(),
        ));

    }

    public function removeAdminAction(string  $projectId, string $user, Request $request)
    {

        // build
        $this->build($projectId);
        if ($this->project->getOwner() != $this->getUser()) {
            throw new HttpException(403, 'Only Owners Can Do That.');
        }

        //data

        // TODO should have CSFR protection here

        $doctrine = $this->getDoctrine()->getManager();
        $userRepository = $doctrine->getRepository('JMBTechnologyUserAccountsBundle:User');
        $projectAdminRepository = $doctrine->getRepository('DirectokiBundle:ProjectAdmin');

        $user = $userRepository->findOneById($user);
        if ($user) {
            $projectAdmin = $projectAdminRepository->findOneBy(array('user' => $user, 'project' => $this->project));

            if ($projectAdmin) {
                $doctrine->remove($projectAdmin);
                $doctrine->flush();
            }
        }

        return $this->redirect($this->generateUrl('directoki_admin_project_user_list', array(
            'projectId'=>$this->project->getPublicId(),
        )));

    }


}
