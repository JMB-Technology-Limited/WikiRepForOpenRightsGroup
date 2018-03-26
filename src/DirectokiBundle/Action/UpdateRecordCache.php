<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordLocaleCache;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateRecordCache
{


    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function go(Record $record) {

        $doctrine = $this->container->get('doctrine')->getManager();
        $recordLocaleCacheRepo = $doctrine->getRepository('DirectokiBundle:RecordLocaleCache');
        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($record->getDirectory());
        $locales = $doctrine->getRepository('DirectokiBundle:Locale')->findByProject($record->getDirectory()->getProject());

        # Record
        $record->setCachedState($doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($record)->getState());
        $record->setCachedNeedsAdminAttention($doctrine->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record) ? 1 : 0);

        $fieldsCache = array();
        foreach($fields as $field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $fieldsCache[$field->getId()] = $fieldType->getDataForCache($field, $record);
        }
        $record->setCachedFields($fieldsCache);

        $doctrine->persist($record);

        # Record Per Locale!
        foreach ($locales as $locale) {

            $recordLocaleCache = $recordLocaleCacheRepo->findOneBy(array('record' => $record, 'locale' => $locale));
            if (!$recordLocaleCache) {
                $recordLocaleCache = new RecordLocaleCache();
                $recordLocaleCache->setRecord($record);
                $recordLocaleCache->setLocale($locale);
            }

            $fullTextSearch = '';
            foreach($fields as $field) {
                $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
                $fullTextSearch .= mb_strtolower($fieldType->getFullTextSearch($field, $record, $locale));
            }
            $recordLocaleCache->setFullTextSearch($fullTextSearch);

            $doctrine->persist($recordLocaleCache);

        }

        $doctrine->flush();

    }



}
