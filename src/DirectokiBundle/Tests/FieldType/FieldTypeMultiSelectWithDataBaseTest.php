<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\Tests\BaseTest;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeMultiSelectWithDatabaseTest extends BaseTestWithDataBase
{


    function testParseCSVLineDataTestAddValueId1() {


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
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setPublicId('m0w');
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('Cats');
        $this->em->persist($selectValueHasTitle);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);


        $fieldConfig = array(
            'add_value_id'=>'m0w',
            'locale' => 'en_GB',
        );
        $lineData = array(
            'cats',
            '3.4',
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('Cats', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('Cats', $result->getEntitiesToSave()[0]->getSelectValue()->getCachedTitleForLocale($locale));
    }



    function testParseCSVLineDataTestAddValueId2() {


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
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $selectValue1 = new SelectValue();
        $selectValue1->setField($field);
        $selectValue1->setPublicId('m0w');
        $selectValue1->setCreationEvent($event);
        $this->em->persist($selectValue1);

        $selectValue1HasTitle = new SelectValueHasTitle();
        $selectValue1HasTitle->setSelectValue($selectValue1);
        $selectValue1HasTitle->setLocale($locale);
        $selectValue1HasTitle->setCreationEvent($event);
        $selectValue1HasTitle->setTitle('Cats');
        $this->em->persist($selectValue1HasTitle);

        $selectValue2 = new SelectValue();
        $selectValue2->setField($field);
        $selectValue2->setPublicId('w0f');
        $selectValue2->setCreationEvent($event);
        $this->em->persist($selectValue2);

        $selectValue2HasTitle = new SelectValueHasTitle();
        $selectValue2HasTitle->setSelectValue($selectValue2);
        $selectValue2HasTitle->setLocale($locale);
        $selectValue2HasTitle->setCreationEvent($event);
        $selectValue2HasTitle->setTitle('Dogs');
        $this->em->persist($selectValue2HasTitle);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);


        $fieldConfig = array(
            'add_value_id'=>'m0w  , ,w0f', // test extra spaces and blank item to
            'locale' => 'en_GB',
        );
        $lineData = array(
            'cats',
            '3.4',
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('Cats, Dogs', $result->getDebugOutput());
        $this->assertEquals(2, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('Cats', $result->getEntitiesToSave()[0]->getSelectValue()->getCachedTitleForLocale($locale));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[1]));
        $this->assertEquals('Dogs', $result->getEntitiesToSave()[1]->getSelectValue()->getCachedTitleForLocale($locale));
    }

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
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
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
            'Test1, Test2, ', // test extra spaces and blank item to
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('New Select Value: Test1, New Select Value: Test2', $result->getDebugOutput());

        $this->assertEquals(6, count($result->getEntitiesToSave()));

        $this->assertEquals("DirectokiBundle\Entity\SelectValue", get_class($result->getEntitiesToSave()[0]));
        // TODO $this->assertEquals('Test1', $result->getEntitiesToSave()[0]->getTitle());

        $this->assertEquals("DirectokiBundle\Entity\SelectValueHasTitle", get_class($result->getEntitiesToSave()[1]));

        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[2]));
        // TODO $this->assertEquals('Test1', $result->getEntitiesToSave()[2]->getSelectValue()->getTitle());

        $this->assertEquals("DirectokiBundle\Entity\SelectValue", get_class($result->getEntitiesToSave()[3]));
        // TODO $this->assertEquals('Test2', $result->getEntitiesToSave()[3]->getTitle());

        $this->assertEquals("DirectokiBundle\Entity\SelectValueHasTitle", get_class($result->getEntitiesToSave()[4]));

        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[5]));
        // TODO $this->assertEquals('Test2', $result->getEntitiesToSave()[5]->getSelectValue()->getTitle());

    }




}

