<?php

namespace DirectokiBundle\InternalAPI\V1;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmailEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLngEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueTextEdit;
use DirectokiBundle\InternalAPI\V1\Model\Record;
use DirectokiBundle\InternalAPI\V1\Model\RecordCreate;
use DirectokiBundle\InternalAPI\V1\Model\RecordEdit;

use DirectokiBundle\InternalAPI\V1\Model\SelectValue;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPI {

    protected $container;

    function __construct( $container ) {
        $this->container = $container;
    }

    /**
     * @param $projectID
     * @return InternalAPIProject
     * @throws \Exception
     */
    function getProjectAPI( string $projectID ) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($projectID);
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        return new InternalAPIProject($this->container, $project);
    }

}
