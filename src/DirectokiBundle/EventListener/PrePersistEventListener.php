<?php

namespace DirectokiBundle\EventListener;



use DirectokiBundle\Entity\Contact;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\SelectValue;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DirectokiBundle\DirectokiBundle;




/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class PrePersistEventListener  {


    const MIN_LENGTH = 5;
    const MAX_LENGTH = 250;
    const LENGTH_STEP = 1;

    function PrePersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if ($entity instanceof Record) {
            if (!$entity->getPublicId()) {
                $manager = $args->getEntityManager()->getRepository('DirectokiBundle\Entity\Record');
                $idLen = self::MIN_LENGTH;
                $id = DirectokiBundle::createKey(1, $idLen);
                while ($manager->doesPublicIdExist($id, $entity->getDirectory())) {
                    if ($idLen < self::MAX_LENGTH) {
                        $idLen = $idLen + self::LENGTH_STEP;
                    }
                    $id = DirectokiBundle::createKey(1, $idLen);
                }
                $entity->setPublicId($id);
            }
        } else if ($entity instanceof Contact) {
            if (!$entity->getPublicId()) {
                $manager = $args->getEntityManager()->getRepository('DirectokiBundle\Entity\Contact');
                $idLen = self::MIN_LENGTH;
                $id = DirectokiBundle::createKey(1, $idLen);
                while ($manager->doesPublicIdExist($id, $entity->getProject())) {
                    if ($idLen < self::MAX_LENGTH) {
                        $idLen = $idLen + self::LENGTH_STEP;
                    }
                    $id = DirectokiBundle::createKey(1, $idLen);
                }
                $entity->setPublicId($id);
            }
        } else if ($entity instanceof SelectValue) {
            if (!$entity->getPublicId()) {
                $manager = $args->getEntityManager()->getRepository('DirectokiBundle\Entity\SelectValue');
                $idLen = self::MIN_LENGTH;
                $id = DirectokiBundle::createKey(1, $idLen);
                while ($manager->doesPublicIdExist($id, $entity->getField())) {
                    if ($idLen < self::MAX_LENGTH) {
                        $idLen = $idLen + self::LENGTH_STEP;
                    }
                    $id = DirectokiBundle::createKey(1, $idLen);
                }
                $entity->setPublicId($id);
            }
        }

    }

}

