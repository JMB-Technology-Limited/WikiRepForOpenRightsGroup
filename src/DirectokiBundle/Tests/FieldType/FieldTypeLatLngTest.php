<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\Tests\BaseTest;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeLatLngTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column_lat'=>1,
            'column_lng'=>2
        );
        $lineData = array(
            'cats',
            '3.4',
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeLatLng($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('3.4, 6.7', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldLatLngValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('3.4', $result->getEntitiesToSave()[0]->getLat());
        $this->assertEquals('6.7', $result->getEntitiesToSave()[0]->getLng());
    }

    function testParseCSVLineDataTest2() {
        $field = new Field();
        $fieldConfig = array(
            'column_lat'=>1,
            'column_lng'=>2
        );
        $lineData = array(
            'cats',
            '',
            '',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeLatLng($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertNull($result);
    }

}
