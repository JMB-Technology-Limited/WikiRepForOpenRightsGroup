<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Contact;
use DirectokiBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EventRepository extends EntityRepository {




    public function deleteIpAndUserAgentFromEventsOlderThan(\DateTime $date) {

        $this->getEntityManager()
            ->createQuery(
                ' UPDATE DirectokiBundle:Event e '.
                ' SET e.IP=null , e.userAgent=null '.
                ' WHERE e.createdAt < :date  '
            )
            ->setParameter('date', $date)
            ->execute();

    }



}

