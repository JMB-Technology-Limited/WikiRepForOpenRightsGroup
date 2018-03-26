<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldStringWithLocaleValueRepository extends EntityRepository {


    public function findLatestFieldValue(Field $field, Record $record, Locale $locale) {

        $s =  $this->getEntityManager()
            ->createQuery(
                ' SELECT fv FROM DirectokiBundle:RecordHasFieldStringWithLocaleValue fv '.
                ' WHERE fv.field = :field AND fv.record = :record AND fv.locale = :locale AND fv.approvedAt IS NOT NULL ' .
                ' ORDER BY fv.approvedAt DESC '
            )
            ->setMaxResults(1)
            ->setParameter('field', $field)
            ->setParameter('record', $record)
            ->setParameter('locale', $locale)
            ->getResult();
        return count($s)  > 0 ? $s[0] : null;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        return $this->getEntityManager()
            ->createQuery(
                ' SELECT fv FROM DirectokiBundle:RecordHasFieldStringWithLocaleValue fv '.
                ' WHERE fv.field = :field AND fv.record = :record AND fv.approvedAt IS  NULL AND fv.refusedAt IS NULL  ' .
                ' ORDER BY fv.createdAt DESC '
            )
            ->setParameter('field', $field)
            ->setParameter('record', $record)
            ->getResult();


    }

}
