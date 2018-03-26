<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\SelectValue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldSelectValueRepository extends EntityRepository {


    public function findLatestFieldValue(Field $field, Record $record) {

        $s =  $this->getEntityManager()
            ->createQuery(
                ' SELECT fv FROM DirectokiBundle:RecordHasFieldSelectValue fv '.
                ' WHERE fv.field = :field AND fv.record = :record AND fv.approvedAt IS NOT NULL ' .
                ' ORDER BY fv.approvedAt DESC, fv.approvalEvent DESC '
            )
            ->setMaxResults(1)
            ->setParameter('field', $field)
            ->setParameter('record', $record)
            ->getResult();
        return count($s)  > 0 ? $s[0] : null;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {


        return $this->getEntityManager()
            ->createQuery(
                ' SELECT fv FROM DirectokiBundle:RecordHasFieldSelectValue fv '.
                ' WHERE fv.field = :field AND fv.record = :record AND fv.approvedAt IS  NULL AND fv.refusedAt IS NULL  ' .
                ' ORDER BY fv.createdAt DESC '
            )
            ->setParameter('field', $field)
            ->setParameter('record', $record)
            ->getResult();


    }

    public function doesRecordHaveFieldHaveValueAwaitingModeration(Record $record, Field $field,  SelectValue $selectValue = null) {

        if (!$record->getId()) {
            // if Record is not saved yet, we just have to assume no values are there.
            return false;
        }

        $s = $this->getEntityManager()
            ->createQuery(
                ' SELECT fv FROM DirectokiBundle:RecordHasFieldSelectValue fv '.
                ' WHERE fv.field = :field AND fv.record = :record AND fv.selectValue = :selectValue '.
                ' AND fv.approvedAt IS NULL AND fv.refusedAt IS NULL'
            )
            ->setParameter('field', $field)
            ->setParameter('record', $record)
            ->setParameter('selectValue', $selectValue)
            ->getResult();

        return count($s)  > 0;

    }


}
