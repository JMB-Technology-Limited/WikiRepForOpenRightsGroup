<?php

namespace DirectokiBundle\Twig;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\FieldType\FieldTypeURL;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DirectoryExtension  extends \Twig_Extension
{


    protected $container;
    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('directoki_directory_count_records_needing_attention', array($this, 'countRecordsNeedingAttention')),
        );
    }

    public function getFunctions()
    {
        return array();
    }

    public function countRecordsNeedingAttention(Directory $directory) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $r = $doctrine
            ->createQuery(
                ' SELECT COUNT(r.id) AS sort FROM DirectokiBundle:Record r'.
                ' WHERE r.directory = :directory AND r.cachedNeedsAdminAttention = true '
            )
            ->setParameter('directory', $directory)
            ->getScalarResult();

        return $r[0]['sort'];
        
    }


}
