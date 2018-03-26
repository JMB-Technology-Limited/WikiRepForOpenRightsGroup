<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1DefaultController extends Controller
{



    public function projectsJSONAction()
    {

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Project');

        $out = array('projects'=>array());
        foreach($repo->findAll() as $project) {
            $out['projects'][] = array(
                'id' => $project->getPublicId(),
                'title' => $project->getTitle(),
            );
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
