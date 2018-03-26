<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class GetPublishedRecordsFieldTypeLatLngWithDataBaseTest extends BaseTestWithDataBase
{




    public function testLatLng1() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle( 'Map' );
        $field->setPublicId( 'map' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeLatLng::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldLatLngValue = new RecordHasFieldLatLngValue();
        $recordHasFieldLatLngValue->setRecord( $record );
        $recordHasFieldLatLngValue->setField( $field );
        $recordHasFieldLatLngValue->setLat( 2.76 );
        $recordHasFieldLatLngValue->setLng( -3.71 );
        $recordHasFieldLatLngValue->setApprovedAt( new \DateTime() );
        $recordHasFieldLatLngValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldLatLngValue );

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getPublishedRecords();

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('map'));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getPublishedRecords();

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng', get_class($records[0]->getFieldValue('map')));
        $this->assertEquals(2.76, $records[0]->getFieldValue('map')->getLat());
        $this->assertEquals(-3.71, $records[0]->getFieldValue('map')->getLng());

    }



}


