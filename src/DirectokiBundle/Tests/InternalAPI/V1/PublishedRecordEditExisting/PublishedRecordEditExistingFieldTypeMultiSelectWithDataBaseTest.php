<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\PublishedRecordEditExisting;


use DirectokiBundle\Cron\UpdateFieldCache;
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
use DirectokiBundle\Entity\SelectValueHasTitle;
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
class PublishedRecordEditExistingFieldTypeMultiSelectWithDataBaseTest extends BaseTestWithDataBase
{



    public function testMultiSelectFieldAdd() {

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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tags');
        $field->setPublicId('tags');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);
        
        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('tags'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelectEdit', get_class($recordEditIntAPI->getFieldValueEdit('tags')));
        $this->assertEquals(0, count( $recordEditIntAPI->getFieldValueEdit('tags')->getSelectValues()));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        # Edit


        $selectValuesFromAPI = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());

        $recordEditIntAPI->getFieldValueEdit('tags')->addValueToAdd($selectValuesFromAPI[0]);
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

        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $record);


        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueAddition', get_class($fieldModerationNeeded));
        $this->assertEquals('PHP', $fieldModerationNeeded->getFieldValue()->getSelectValue()->getCachedTitleForLocale($locale));

    }


    public function testMultiSelectFieldRemove() {

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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tags');
        $field->setPublicId('tags');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $recordHasFieldMultiSelectValue = new RecordHasFieldMultiSelectValue();
        $recordHasFieldMultiSelectValue->setField($field);
        $recordHasFieldMultiSelectValue->setSelectValue($selectValue);
        $recordHasFieldMultiSelectValue->setRecord($record);
        $recordHasFieldMultiSelectValue->setAdditionCreationEvent($event);
        $recordHasFieldMultiSelectValue->setAdditionApprovalEvent($event);
        $this->em->persist($recordHasFieldMultiSelectValue);


        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('tags'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelectEdit', get_class($recordEditIntAPI->getFieldValueEdit('tags')));
        $selectValues = $recordEditIntAPI->getFieldValueEdit('tags')->getSelectValues();
        $this->assertEquals(1, count( $selectValues ));
        $this->assertEquals('PHP', $selectValues[0]->getTitle());


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        # Edit


        $selectValuesFromAPI = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());

        $recordEditIntAPI->getFieldValueEdit('tags')->addValueToRemove($selectValuesFromAPI[0]);
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

        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $record);


        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueRemoval', get_class($fieldModerationNeeded));
        $this->assertEquals('PHP', $fieldModerationNeeded->getFieldValue()->getSelectValue()->getCachedTitleForLocale($locale));

    }





    public function testMultiSelectFieldAddOneAlreadyAdded() {

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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tags');
        $field->setPublicId('tags');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);


        $recordHasFieldMultiSelectValue = new RecordHasFieldMultiSelectValue();
        $recordHasFieldMultiSelectValue->setField($field);
        $recordHasFieldMultiSelectValue->setSelectValue($selectValue);
        $recordHasFieldMultiSelectValue->setRecord($record);
        $recordHasFieldMultiSelectValue->setAdditionCreationEvent($event);
        $recordHasFieldMultiSelectValue->setAdditionApprovalEvent($event);
        $this->em->persist($recordHasFieldMultiSelectValue);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('tags'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelectEdit', get_class($recordEditIntAPI->getFieldValueEdit('tags')));
        $this->assertEquals(1, count( $recordEditIntAPI->getFieldValueEdit('tags')->getSelectValues()));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        # Edit


        $selectValuesFromAPI = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());

        $recordEditIntAPI->getFieldValueEdit('tags')->addValueToAdd($selectValuesFromAPI[0]);
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertFalse($result->getSuccess());
        $this->assertFalse($result->isApproved());


        # TEST


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

    }


    public function testMultiSelectFieldAddOneAlreadyAwaitingModeration() {

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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tags');
        $field->setPublicId('tags');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('tags'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelectEdit', get_class($recordEditIntAPI->getFieldValueEdit('tags')));
        $this->assertEquals(0, count( $recordEditIntAPI->getFieldValueEdit('tags')->getSelectValues()));


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        # Edit ONCE


        $selectValuesFromAPI = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());

        $recordEditIntAPI->getFieldValueEdit('tags')->addValueToAdd($selectValuesFromAPI[0]);
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        // first time it saves
        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertFalse($result->isApproved());

        # Edit TWICE


        $selectValuesFromAPI = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());

        $recordEditIntAPI->getFieldValueEdit('tags')->addValueToAdd($selectValuesFromAPI[0]);
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        // it is already saved so returns false. Maybe it should return true tho?
        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertFalse($result->getSuccess());
        $this->assertFalse($result->isApproved());


        # TEST IT is only there once


        $this->assertTrue($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $record);


        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueAddition', get_class($fieldModerationNeeded));
        $this->assertEquals('PHP', $fieldModerationNeeded->getFieldValue()->getSelectValue()->getCachedTitleForLocale($locale));


    }




}
