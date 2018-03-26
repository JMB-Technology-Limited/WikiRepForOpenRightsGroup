<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectController extends Controller
{


    /** @var Project */
    protected $project;


    protected function build(string $projectId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
    }


    public function indexAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $locales = $doctrine->getRepository('DirectokiBundle:Locale')->findByProject($this->project);

        return $this->render('DirectokiBundle:AdminProject:index.html.twig', array(
            'project' => $this->project,
            'locales' => $locales,
        ));

    }

    public function userAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:ProjectAdmin');
        $projectAdmins = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:AdminProject:user.html.twig', array(
            'project' => $this->project,
            'projectAdmins' => $projectAdmins,
        ));

    }

    public function contactAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Contact');
        $contacts = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:AdminProject:contact.html.twig', array(
            'project' => $this->project,
            'contacts' => $contacts,
        ));

    }


    public function historyAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Event');
        $histories = $repo->findBy(array('project'=>$this->project),array('createdAt'=>'desc'),1000);

        return $this->render('DirectokiBundle:AdminProject:history.html.twig', array(
            'project' => $this->project,
            'histories' => $histories,
        ));

    }


    public function statsAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Event');
        $histories = count($repo->findBy(array('project'=>$this->project),array('createdAt'=>'desc')));

        return $this->render('DirectokiBundle:AdminProject:stats.html.twig', array(
            'project' => $this->project,
            'histories' => $histories,
        ));

    }


    public function settingsAction(string $projectId)
    {
        // build
        $this->build($projectId);
        //data
        return $this->render('DirectokiBundle:AdminProject:settings.html.twig', array(
            'project' => $this->project,
        ));
    }



}
