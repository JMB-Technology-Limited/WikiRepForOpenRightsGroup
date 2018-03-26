<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Contact;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class SelectValueRepository extends EntityRepository {


    public function doesPublicIdExist(string $id, Field $field)
    {
        if ($field->getId()) {
            $s =  $this->getEntityManager()
                       ->createQuery(
                           ' SELECT sv FROM DirectokiBundle:SelectValue sv'.
                           ' WHERE sv.field = :field AND sv.publicId = :public_id'
                       )
                       ->setParameter('field', $field)
                       ->setParameter('public_id', $id)
                       ->getResult();
            return (boolean)$s;
        } else {
            return false;
        }
    }

    public function findByTitleFromUser(Field $field, string $title, Locale $locale) {

        $selectValueHaveTitles =  $this->getEntityManager()
            ->createQuery(
                ' SELECT svht FROM DirectokiBundle:SelectValueHasTitle svht '.
                ' JOIN svht.selectValue sv '.
                ' WHERE sv.field = :field AND svht.locale = :locale AND TRIM(LOWER(svht.title)) = :title'
            )
            ->setParameter('field', $field)
            ->setParameter('locale', $locale)
            ->setParameter('title', mb_strtolower(trim($title)))
            ->getResult();


        $repo = $this->getEntityManager()->getRepository('DirectokiBundle:SelectValueHasTitle');

        foreach($selectValueHaveTitles as $selectValueHaveTitle) {
            // We could have found a value that is old - load the latest value for this locale and check by hand to make sure.
            $titleValueForLocale = $repo->findOneBy(
                ['selectValue'=>$selectValueHaveTitle->getSelectValue(), 'locale'=>$locale],
                ['createdAt'=>'desc']
            );
            if (mb_strtolower(trim($title)) == mb_strtolower(trim($titleValueForLocale->getTitle()))) {
                return $selectValueHaveTitle->getSelectValue();
            }
        }

    }

    public function findByFieldSortForLocale(Field $field, Locale $locale) {


        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('DirectokiBundle\Entity\SelectValue', 'sv');

        $s = $this->getEntityManager()
            ->createNativeQuery(
                ' SELECT sv.id, sv.public_id, sv.cached_titles, sv.field_id FROM directoki_select_value sv ' .
                ' WHERE sv.field_id = :field ' .
                ' ORDER BY sv.cached_titles->>\'' . $locale->getId(). '\' ASC',
                $rsm
            )
            ->setParameter('field', $field->getId())
            ->getResult();

        return $s;

    }

}

