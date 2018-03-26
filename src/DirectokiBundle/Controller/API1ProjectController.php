<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectController extends Controller
{

    use API1TraitLocale;

    /** @var Project */
    protected $project;

    protected function build(string $projectId, Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        // Project
        $projectRepository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $projectRepository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }

        if (!$this->project->isAPIReadAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }
        // TODO check isAPIReadAllowed
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);

        $this->buildLocale($request);
    }


    public function indexJSONAction(string $projectId, Request $request)
    {

        // build
        $this->build($projectId, $request);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');
        $directories = $repo->findByProject($this->project);

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            )
        );

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }



    public function directoriesJSONAction(string $projectId, Request $request)
    {

        // build
        $this->build($projectId, $request);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directories'=>array(),
        );

        foreach($repo->findByProject($this->project) as $directory) {
            $out['directories'][] = array(
                'id' => $directory->getPublicId(),
                'title_singular' => $directory->getTitleSingular(),
                'title_plural' => $directory->getTitlePlural(),
            );
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function localesJSONAction(string $projectId, Request $request)
    {

        // build
        $this->build($projectId, $request);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Locale');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'locales'=>array(),
        );

        foreach($repo->findByProject($this->project) as $locale) {
            $out['locales'][] = array(
                'id' => $locale->getPublicId(),
                'title' => $locale->getTitle(),
            );
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

}
