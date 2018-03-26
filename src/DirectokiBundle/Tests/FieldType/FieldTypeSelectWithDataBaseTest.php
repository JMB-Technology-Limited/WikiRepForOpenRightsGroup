<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTest;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeSelectWithDataBaseTest extends BaseTestWithDataBase
{

    function testParseCSVLineDataTestAddTitleColumn1() {


        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $locale = new Locale();
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
        $locale->setCreationEvent($event);
        $this->em->persist($locale);

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $field = new Field();
        $field->setTitle( 'Topic' );
        $field->setPublicId( 'topic' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );


        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);


        $fieldConfig = array(
            'add_title_column'=>'1',
            'locale' => 'en_GB',
        );
        $lineData = array(
            'cats',
            'Test1 ', // test extra spaces
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;

        $fieldType = new FieldTypeSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('New Select Value: Test1', $result->getDebugOutput());

        $this->assertEquals(3, count($result->getEntitiesToSave()));

        $this->assertEquals("DirectokiBundle\Entity\SelectValue", get_class($result->getEntitiesToSave()[0]));
        // TODO $this->assertEquals('Test1', $result->getEntitiesToSave()[0]->getTitle());

        $this->assertEquals("DirectokiBundle\Entity\SelectValueHasTitle", get_class($result->getEntitiesToSave()[1]));

        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldSelectValue", get_class($result->getEntitiesToSave()[2]));
        // TODO $this->assertEquals('Test1', $result->getEntitiesToSave()[2]->getSelectValue()->getTitle());

    }



}
