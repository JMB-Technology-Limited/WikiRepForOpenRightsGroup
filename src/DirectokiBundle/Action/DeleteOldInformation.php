<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Project;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DeleteOldInformation
{


    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function go() {

        $hours = $this->container->getParameter('directoki.delete_information_after_hours');

        if ($hours > 0) {

            $doctrine = $this->container->get('doctrine')->getManager();

            $date = $this->container->get('directoki.time_service')->getDateTimeNowUTC();
            $date->sub(new \DateInterval('PT' .$hours.'H'));

            $doctrine->getRepository('DirectokiBundle:Event')
                ->deleteIpAndUserAgentFromEventsOlderThan($date);

        }
    }

}
