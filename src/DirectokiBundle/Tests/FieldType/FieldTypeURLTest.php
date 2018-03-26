<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Tests\BaseTest;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeURLTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            'https://www.google.co.uk',
            'http://example.com/'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeURL($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('https://www.google.co.uk', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldURLValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('https://www.google.co.uk', $result->getEntitiesToSave()[0]->getValue());
    }

}
