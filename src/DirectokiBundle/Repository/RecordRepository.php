<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\RecordsInDirectoryQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordRepository extends EntityRepository {


    public function findByRecordsInDirectoryQuery(RecordsInDirectoryQuery $recordInDirectoryQuery) {
        $where = array(
            'r.directory = :directory '
        );
        $joins = array();
        $params = array(
            'directory'=>$recordInDirectoryQuery->getDirectory(),
        );

        if ($recordInDirectoryQuery->isPublishedOnly()) {
            $where[] = 'r.cachedState = :cachedState';
            $params['cachedState'] = RecordHasState::STATE_PUBLISHED;
        }

        if ($recordInDirectoryQuery->getFullTextSearch() && $recordInDirectoryQuery->getLocale()) {
            $joins[] = " JOIN r.recordLocaleCaches rlc WITH rlc.locale = :locale ";
            $params['locale'] = $recordInDirectoryQuery->getLocale();
            $where[] = ' rlc.fullTextSearch LIKE :fullTextSearch';
            $params['fullTextSearch'] = '%'.mb_strtolower($recordInDirectoryQuery->getFullTextSearch()).'%';
        }

        $query =  $this->getEntityManager()
            ->createQuery(
                ' SELECT r FROM DirectokiBundle:Record r '.
                implode(' ', $joins).
                ' WHERE '. implode(' AND ', $where)
            );;

        foreach($params as $k=>$v) {
            $query->setParameter($k, $v);
        }

        return $query->getResult();
    }


    public function doesPublicIdExist(string $id, Directory $directory)
    {
        if ($directory->getId()) {
            $s =  $this->getEntityManager()
                       ->createQuery(
                           ' SELECT r FROM DirectokiBundle:Record r'.
                           ' WHERE r.directory = :directory AND r.publicId = :public_id'
                       )
                       ->setParameter('directory', $directory)
                       ->setParameter('public_id', $id)
                       ->getResult();
            return (boolean)$s;
        } else {
            return false;
        }
    }


    public function doesRecordNeedAdminAttention(Record $record) {

        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldStringValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldStringWithLocaleValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldTextValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldLatLngValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldEmailValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldURLValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldBooleanValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }

        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldDateValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }

        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldMultiSelectValue r'.
                ' WHERE r.record = :record AND r.additionApprovedAt IS NULL AND r.additionRefusedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }

        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldSelectValue r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }

        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasFieldMultiSelectValue r'.
                ' WHERE r.record = :record AND r.removalCreatedAt IS NOT NULL AND r.removalApprovedAt IS NULL AND r.removalRefusedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordHasState r'.
                ' WHERE r.record = :record AND r.refusedAt IS NULL AND r.approvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }


        $count =  $this->getEntityManager()
            ->createQuery(
                ' SELECT count(r) FROM DirectokiBundle:RecordReport r'.
                ' WHERE r.record = :record AND r.resolvedAt IS NULL '
            )
            ->setParameter('record', $record)
            ->getSingleScalarResult();
        if ($count > 0) {
            return true;
        }

        return false;
    }


    public function getRecordsNeedingAttention(Directory $directory) {

        return $this->getEntityManager()
            ->createQuery(
                ' SELECT r FROM DirectokiBundle:Record r'.
                ' WHERE r.directory = :directory AND r.cachedNeedsAdminAttention = :attention '.
                ' GROUP BY r.id '
            )
            ->setParameter('directory', $directory)
            ->setParameter('attention', true)
            ->getResult();
    }


}
