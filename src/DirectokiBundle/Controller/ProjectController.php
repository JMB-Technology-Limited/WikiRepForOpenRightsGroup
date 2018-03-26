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
class ProjectController extends Controller
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
        if (!$this->project->isWebReadAllowed()) {
            throw new NotFoundHttpException('Read Feature Not Found');
        }
    }

    public function indexAction(string $projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Locale');
        $locales = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:Project:index.html.twig', array(
            'project' => $this->project,
            'locales' => $locales,
        ));

    }

}
