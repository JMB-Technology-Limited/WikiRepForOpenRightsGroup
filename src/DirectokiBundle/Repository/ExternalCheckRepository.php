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
class ExternalCheckRepository extends EntityRepository {


    public function wasURLCheckedRecently(string $url, Project $project) {

        $date = new \DateTime();
        // TODO no magic numbers, take time period from configuration
        $date->sub(new \DateInterval('P30D'));

        $count = $this->getEntityManager()
            ->createQuery(
                ' SELECT count(ec) FROM DirectokiBundle:ExternalCheck ec '.
                ' WHERE ec.url = :url AND ec.project = :project AND ec.createdAt > :date  '
            )
            ->setParameter('url', $url)
            ->setParameter('project', $project)
            ->setParameter('date', $date)
            ->getSingleScalarResult();

        return (bool)$count;
    }

}

