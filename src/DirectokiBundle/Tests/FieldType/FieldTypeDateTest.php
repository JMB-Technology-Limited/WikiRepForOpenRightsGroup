<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTest;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeDateTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            '2017-01-01',
            'dogs'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeDate($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('2017-01-01', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldDateValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('2017-01-01', $result->getEntitiesToSave()[0]->getValue()->format('Y-m-d'));
    }

}
