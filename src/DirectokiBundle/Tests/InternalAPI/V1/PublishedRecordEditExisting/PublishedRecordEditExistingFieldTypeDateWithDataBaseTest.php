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
use DirectokiBundle\Entity\RecordHasFieldDateValue;
use DirectokiBundle\Entity\RecordHasFieldDateWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\FieldType\FieldTypeDateWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class PublishedRecordEditExistingFieldTypeDateWithDataBaseTest extends BaseTestWithDataBase {




    public function testDateField() {

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
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setCreationEvent($event);
        $recordHasFieldDateValue->setApprovalEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('date'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueDateEdit', get_class($recordEditIntAPI->getFieldValueEdit('date')));
        $this->assertEquals('Date', $recordEditIntAPI->getFieldValueEdit('date')->getTitle());
        $this->assertEquals('2016-01-01', $recordEditIntAPI->getFieldValueEdit('date')->getValue()->format('Y-m-d'));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('date')->setNewValue(new \DateTime('2017-01-01'));
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');

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

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldDateValue', get_class($fieldModerationNeeded));
        $this->assertEquals('2017-01-01', $fieldModerationNeeded->getValue()->format('Y-m-d'));


    }


    public function testDateFieldToNull() {

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
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setCreationEvent($event);
        $recordHasFieldDateValue->setApprovalEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('date'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueDateEdit', get_class($recordEditIntAPI->getFieldValueEdit('date')));
        $this->assertEquals('Date', $recordEditIntAPI->getFieldValueEdit('date')->getTitle());
        $this->assertEquals('2016-01-01', $recordEditIntAPI->getFieldValueEdit('date')->getValue()->format('Y-m-d'));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('date')->setNewValue(null);
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');

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

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldDateValue', get_class($fieldModerationNeeded));
        $this->assertNull($fieldModerationNeeded->getValue());


    }



    public function testApproveInstantlyIfAllowedWhenNotAllowed() {


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
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setCreationEvent($event);
        $recordHasFieldDateValue->setApprovalEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('date'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueDateEdit', get_class($recordEditIntAPI->getFieldValueEdit('date')));
        $this->assertEquals('Date', $recordEditIntAPI->getFieldValueEdit('date')->getTitle());
        $this->assertEquals('2016-01-01', $recordEditIntAPI->getFieldValueEdit('date')->getValue()->format('Y-m-d'));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        # Edit

        $recordEditIntAPI->getFieldValueEdit('date')->setNewValue(new \DateTime('2017-01-01'));
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(true);

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

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldDateValue', get_class($fieldModerationNeeded));
        $this->assertEquals('2017-01-01', $fieldModerationNeeded->getValue()->format('Y-m-d'));

    }





    public function testApproveInstantlyIfAllowedWhenAllowed() {

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
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setCreationEvent($event);
        $recordHasFieldDateValue->setApprovalEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('date'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueDateEdit', get_class($recordEditIntAPI->getFieldValueEdit('date')));
        $this->assertEquals('Date', $recordEditIntAPI->getFieldValueEdit('date')->getTitle());
        $this->assertEquals('2016-01-01', $recordEditIntAPI->getFieldValueEdit('date')->getValue()->format('Y-m-d'));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('date')->setNewValue(new \DateTime('2017-01-01'));
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setUser($user);
        $recordEditIntAPI->setApproveInstantlyIfAllowed(true);

        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertTrue($result->isApproved());


        # TEST


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        $records = $this->em->getRepository('DirectokiBundle:Record')->findByDirectory($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $this->assertEquals('2017-01-01', $fieldType->getLatestFieldValues($field, $records[0])[0]->getValue()->format('Y-m-d'));

    }


}

