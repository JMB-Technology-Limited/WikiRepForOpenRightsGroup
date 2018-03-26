<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\Tests\BaseTest;
use DirectokiBundle\Exception\DataValidationException;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeEmailTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            'cats@example.com',
            'dogs@example.com'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeEmail($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('cats@example.com', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldEmailValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('cats@example.com', $result->getEntitiesToSave()[0]->getValue());
    }

    function testParseCSVLineDataTestNotAnEmailTest() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            'catsExampleWoof',
            'dogs@example.com'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeEmail($this->container);
        $this->expectException(DataValidationException::class);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);

    }

}
