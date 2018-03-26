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
use DirectokiBundle\InternalAPI\V1\Query\RecordsInDirectoryQuery;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class GetPublishedRecordsWithFullTextSearchFieldTypeStringWithDataBaseTest extends BaseTestWithDataBase
{

    public function testString1() {

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

        $locale = new Locale();
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
        $locale->setProject($project);
        $locale->setCreationEvent($event);
        $this->em->persist($locale);

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

        # TEST FIND, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $internalAPIProject = $internalAPI->getProjectAPI('test1');
        $query = new RecordsInDirectoryQuery($internalAPIProject->getLocaleByPublicId('en_GB'));
        $query->setFullTextSearch('title');
        $records = $internalAPIProject->getDirectoryAPI('resource')->getPublishedRecords($query);

        $this->assertEquals(0, count($records));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST FIND, CACHE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIProject = $internalAPI->getProjectAPI('test1');
        $query = new RecordsInDirectoryQuery($internalAPIProject->getLocaleByPublicId('en_GB'));
        $query->setFullTextSearch('title'); // deliberately searching a different case!
        $records = $internalAPIProject->getDirectoryAPI('resource')->getPublishedRecords($query);

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueString', get_class($records[0]->getFieldValue('title')));
        $this->assertEquals('My Title Rocks', $records[0]->getFieldValue('title')->getValue());

        # TEST NO FIND, CACHE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIProject = $internalAPI->getProjectAPI('test1');
        $query = new RecordsInDirectoryQuery($internalAPIProject->getLocaleByPublicId('en_GB'));
        $query->setFullTextSearch('cats');
        $records = $internalAPIProject->getDirectoryAPI('resource')->getPublishedRecords($query);

        $this->assertEquals(0, count($records));


    }





}


