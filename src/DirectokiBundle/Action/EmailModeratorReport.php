<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Project;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EmailModeratorReport
{


    protected $container;

    /** @var  Project */
    protected $project;


    public function __construct($container, Project $project)
    {
        $this->container = $container;
        $this->project = $project;
    }


    public function getRecordsToList() {


        $out = array();

        $doctrine = $this->container->get('doctrine')->getManager();
        $repoDirectories = $doctrine->getRepository('DirectokiBundle:Directory');
        $repoRecords = $doctrine->getRepository('DirectokiBundle:Record');


        foreach($repoDirectories->findByProject($this->project) as $directory) {

            $out = array_merge($out, $repoRecords->getRecordsNeedingAttention($directory));

        }


        return $out;

    }






}
