<?php

namespace DirectokiBundle\Service;

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
class TimeService
{

    public function getDateTimeNowUTC() {
        return new \DateTime('', new \DateTimeZone('UTC'));
    }

}
