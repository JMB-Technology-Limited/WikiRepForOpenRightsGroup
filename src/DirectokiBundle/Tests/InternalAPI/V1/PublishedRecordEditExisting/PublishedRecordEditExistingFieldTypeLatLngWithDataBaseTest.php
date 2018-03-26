<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\PublishedRecordEditExisting;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeEmail;
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
class PublishedRecordEditExistingFieldTypeLatLngWithDataBaseTest extends BaseTestWithDataBase
{



    public function testLatLngField() {

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
        $field->setTitle('Map');
        $field->setPublicId('map');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldLatLngValue = new RecordHasFieldLatLngValue();
        $recordHasFieldLatLngValue->setRecord($record);
        $recordHasFieldLatLngValue->setField($field);
        $recordHasFieldLatLngValue->setLat(34.92);
        $recordHasFieldLatLngValue->setLng(-1.76);
        $recordHasFieldLatLngValue->setApprovedAt(new \DateTime());
        $recordHasFieldLatLngValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldLatLngValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('map'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLngEdit', get_class($recordEditIntAPI->getFieldValueEdit('map')));
        $this->assertEquals('Map', $recordEditIntAPI->getFieldValueEdit('map')->getTitle());
        $this->assertEquals(34.92, $recordEditIntAPI->getFieldValueEdit('map')->getLat());
        $this->assertEquals(-1.76, $recordEditIntAPI->getFieldValueEdit('map')->getLng());


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('map')->setNewLat(12.82);
        $recordEditIntAPI->getFieldValueEdit('map')->setNewLng(-9.82);
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertFalse($result->isApproved());




        # TEST


        $this->assertTrue($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);



        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $record);



        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldLatLngValue', get_class($fieldModerationNeeded));
        $this->assertEquals(12.82, $fieldModerationNeeded->getLat());
        $this->assertEquals(-9.82, $fieldModerationNeeded->getLng());


    }


}