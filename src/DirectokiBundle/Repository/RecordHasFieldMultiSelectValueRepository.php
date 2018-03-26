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
class RecordHasFieldMultiSelectValueRepository extends EntityRepository {


    public function findLatestFieldValues(Field $field, Record $record) {

        return $this->getEntityManager()
                   ->createQuery(
                       ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                       ' WHERE fv.field = :field AND fv.record = :record AND fv.additionApprovedAt IS NOT NULL AND fv.removalApprovedAt IS NULL'
                   )
                   ->setParameter('field', $field)
                   ->setParameter('record', $record)
                   ->getResult();

    }

    public function doesRecordHaveFieldHaveValue(Record $record, Field $field,  SelectValue $selectValue) {

        if (!$record->getId()) {
            // if Record is not saved yet, we just have to assume no values are there.
            return false;
        }

        $s = $this->getEntityManager()
                    ->createQuery(
                        ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                        ' WHERE fv.field = :field AND fv.record = :record AND fv.selectValue = :selectValue AND fv.additionApprovedAt IS NOT NULL AND fv.removalApprovedAt IS NULL'
                    )
                    ->setParameter('field', $field)
                    ->setParameter('record', $record)
                    ->setParameter('selectValue', $selectValue)
                    ->getResult();

        return count($s)  > 0;

    }

    public function doesRecordHaveFieldHaveValueAwaitingModeration(Record $record, Field $field,  SelectValue $selectValue) {

        if (!$record->getId()) {
            // if Record is not saved yet, we just have to assume no values are there.
            return false;
        }

        $s = $this->getEntityManager()
                    ->createQuery(
                        ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                        ' WHERE fv.field = :field AND fv.record = :record AND fv.selectValue = :selectValue '.
                        ' AND fv.additionApprovedAt IS NULL AND fv.additionRefusedAt IS NULL'
                    )
                    ->setParameter('field', $field)
                    ->setParameter('record', $record)
                    ->setParameter('selectValue', $selectValue)
                    ->getResult();

        return count($s)  > 0;

    }

    public function getRecordFieldHasValue(Record $record, Field $field,  SelectValue $selectValue) {

        $s = $this->getEntityManager()
                    ->createQuery(
                        ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                        ' WHERE fv.field = :field AND fv.record = :record AND fv.selectValue = :selectValue AND fv.additionApprovedAt IS NOT NULL AND fv.removalApprovedAt IS NULL'
                    )
                    ->setParameter('field', $field)
                    ->setParameter('record', $record)
                    ->setParameter('selectValue', $selectValue)
                    ->getResult();

        return count($s)  > 0 ? $s[0] : null ;

    }

    public function getAdditionFieldValuesToModerate(Field $field, Record $record) {

        return $this->getEntityManager()
                    ->createQuery(
                        ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                        ' WHERE fv.field = :field AND fv.record = :record AND fv.additionApprovedAt IS  NULL AND fv.additionRefusedAt IS NULL  ' .
                        ' ORDER BY fv.additionCreatedAt DESC '
                    )
                    ->setParameter('field', $field)
                    ->setParameter('record', $record)
                    ->getResult();


    }

    public function getRemovalFieldValuesToModerate(Field $field, Record $record) {

        return $this->getEntityManager()
                    ->createQuery(
                        ' SELECT fv FROM DirectokiBundle:RecordHasFieldMultiSelectValue fv '.
                        ' WHERE fv.field = :field AND fv.record = :record AND fv.removalCreatedAt IS NOT NULL AND fv.removalApprovedAt IS  NULL AND fv.removalRefusedAt IS NULL  ' .
                        ' ORDER BY fv.removalCreatedAt DESC '
                    )
                    ->setParameter('field', $field)
                    ->setParameter('record', $record)
                    ->getResult();


    }


}
