<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminDefaultController extends Controller
{


    public function projectsAction()
    {

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Project');
        $projects = $repo->findAll();



        return $this->render('DirectokiBundle:AdminDefault:projects.html.twig', array(
            'projects' => $projects,
        ));
    }


}
