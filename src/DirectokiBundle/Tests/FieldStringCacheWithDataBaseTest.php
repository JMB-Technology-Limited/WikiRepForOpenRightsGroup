<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 * @TODO Move into FieldType package
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldStringCacheWithDataBaseTest extends BaseTestWithDataBase {

    public function test1() {

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

        # TEST, WE HAVE NO CACHE
        $record = $this->em->getRepository('DirectokiBundle:Record')->find($record->getId());
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $cachedValues = $fieldType->getLatestFieldValuesFromCache($field, $record);
        $this->assertEquals(1, count($cachedValues));
        $this->assertNull($cachedValues[0]);


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, WE HAVE CACHE
        $record = $this->em->getRepository('DirectokiBundle:Record')->find($record->getId());
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $cachedValues = $fieldType->getLatestFieldValuesFromCache($field, $record);
        $this->assertEquals(1, count($cachedValues));
        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringValue', get_class($cachedValues[0]));
        $this->assertEquals('My Title Rocks', $cachedValues[0]->getValue());


    }

}

