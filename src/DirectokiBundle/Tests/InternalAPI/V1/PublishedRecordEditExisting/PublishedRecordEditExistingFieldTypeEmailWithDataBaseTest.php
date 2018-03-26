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
class PublishedRecordEditExistingFieldTypeEmailWithDataBaseTest extends BaseTestWithDataBase
{


    public function testEmailField() {

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
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldEmailValue = new RecordHasFieldEmailValue();
        $recordHasFieldEmailValue->setRecord($record);
        $recordHasFieldEmailValue->setField($field);
        $recordHasFieldEmailValue->setValue('bob@example.com');
        $recordHasFieldEmailValue->setApprovedAt(new \DateTime());
        $recordHasFieldEmailValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldEmailValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('email'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueEmailEdit', get_class($recordEditIntAPI->getFieldValueEdit('email')));
        $this->assertEquals('Email', $recordEditIntAPI->getFieldValueEdit('email')->getTitle());
        $this->assertEquals('bob@example.com', $recordEditIntAPI->getFieldValueEdit('email')->getValue());


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('email')->setNewValue('linda@example.com');
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

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldEmailValue', get_class($fieldModerationNeeded));
        $this->assertEquals('linda@example.com', $fieldModerationNeeded->getValue());


    }


    public function testNotAnEmail() {

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
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldEmailValue = new RecordHasFieldEmailValue();
        $recordHasFieldEmailValue->setRecord($record);
        $recordHasFieldEmailValue->setField($field);
        $recordHasFieldEmailValue->setValue('bob@example.com');
        $recordHasFieldEmailValue->setApprovedAt(new \DateTime());
        $recordHasFieldEmailValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldEmailValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('email'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueEmailEdit', get_class($recordEditIntAPI->getFieldValueEdit('email')));
        $this->assertEquals('Email', $recordEditIntAPI->getFieldValueEdit('email')->getTitle());
        $this->assertEquals('bob@example.com', $recordEditIntAPI->getFieldValueEdit('email')->getValue());


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('email')->setNewValue('lindaMadeAType@gmail,com');
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertFalse($result->getSuccess());
        $this->assertFalse($result->isApproved());
        $this->assertTrue($result->hasFieldErrors());

        $this->assertTrue($result->hasFieldErrorsForField('email'));

        $errors = $result->getFieldErrorsForField('email');
        $this->assertEquals(1, count($errors));
        $this->assertEquals('Please set a valid email!', $errors[0]->getMessage());





    }


}