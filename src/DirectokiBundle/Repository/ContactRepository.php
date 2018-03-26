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
class ContactRepository extends EntityRepository {


    public function doesPublicIdExist(string $id, Project $project)
    {
        if ($project->getId()) {
            $s =  $this->getEntityManager()
                       ->createQuery(
                           ' SELECT c FROM DirectokiBundle:Contact c'.
                           ' WHERE c.project = :project AND c.publicId = :public_id'
                       )
                       ->setParameter('project', $project)
                       ->setParameter('public_id', $id)
                       ->getResult();
            return (boolean)$s;
        } else {
            return false;
        }
    }

    public function findOrCreateByEmail(Project $project, string $email) {

        $contact = $this->findOneBy(array('project'=>$project, 'email'=>$email));

        if ($contact) {
            return $contact;
        }

        $contact = new Contact();
        $contact->setProject($project);
        $contact->setEmail($email);

        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush($contact);

        return $contact;

    }



}

