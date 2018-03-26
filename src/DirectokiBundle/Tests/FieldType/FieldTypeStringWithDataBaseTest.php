<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeStringWithDataBaseTest extends BaseTestWithDataBase
{

    function testExportCSV1() {

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

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $record = new Record();
        $record->setDirectory( $directory );
        $record->setCreationEvent( $event );
        $record->setCachedState( RecordHasState::STATE_DRAFT );
        $record->setPublicId( 'r1' );
        $this->em->persist( $record );

        $field = new Field();
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldStringValue = new RecordHasFieldStringValue();
        $recordHasFieldStringValue->setRecord( $record );
        $recordHasFieldStringValue->setField( $field );
        $recordHasFieldStringValue->setValue( 'My Title Rocks' );
        $recordHasFieldStringValue->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue );

        $this->em->flush();


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $headers = $fieldType->getExportCSVHeaders($field);
        $this->assertEquals(1, count($headers));
        $this->assertEquals('Title', $headers[0]);

        $datas = $fieldType->getExportCSVData($field, $record);
        $this->assertEquals(1, count($datas));
        $this->assertEquals('My Title Rocks', $datas[0]);



    }

    function testGetURLsForExternalCheck1() {

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

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $record = new Record();
        $record->setDirectory( $directory );
        $record->setCreationEvent( $event );
        $record->setCachedState( RecordHasState::STATE_DRAFT );
        $record->setPublicId( 'r1' );
        $this->em->persist( $record );

        $field = new Field();
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldStringValue = new RecordHasFieldStringValue();
        $recordHasFieldStringValue->setRecord( $record );
        $recordHasFieldStringValue->setField( $field );
        $recordHasFieldStringValue->setValue( 'My Title Rocks but visit http://www.directoki.org/ for more!' );
        $recordHasFieldStringValue->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue );

        $this->em->flush();


        # TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $urls = $fieldType->getURLsForExternalCheck($field, $record);
        $this->assertEquals(1, count($urls));
        $this->assertEquals('http://www.directoki.org/', $urls[0]);

    }

}
