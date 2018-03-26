<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTest;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeStringTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            'cats',
            'dogs'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeString($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('cats', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldStringValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('cats', $result->getEntitiesToSave()[0]->getValue());
    }

}
