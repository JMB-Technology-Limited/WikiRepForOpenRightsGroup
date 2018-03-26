<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DefaultController extends Controller
{

    public function indexAction()
    {
        return $this->render('DirectokiBundle:Default:index.html.twig');
    }

    public function projectsAction()
    {

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Project');
        $projects = $repo->findBy(array('WebReadAllowed'=>true));



        return $this->render('DirectokiBundle:Default:projects.html.twig', array(
            'projects' => $projects,
        ));
    }


}
