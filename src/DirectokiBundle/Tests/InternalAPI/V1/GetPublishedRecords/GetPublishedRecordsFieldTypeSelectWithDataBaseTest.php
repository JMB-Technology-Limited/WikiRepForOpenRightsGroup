<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeSelect;
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
class GetPublishedRecordsFieldTypeSelectWithDataBaseTest extends BaseTestWithDataBase
{


    public function testMultiSelect1() {

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
        $field->setTitle('Topic');
        $field->setPublicId('topic');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setPublicId('cats');
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('Cats');
        $this->em->persist($selectValueHasTitle);

        $recordHasFieldSelectValue = new RecordHasFieldSelectValue();
        $recordHasFieldSelectValue->setRecord($record);
        $recordHasFieldSelectValue->setField($field);
        $recordHasFieldSelectValue->setSelectValue($selectValue);
        $recordHasFieldSelectValue->setApprovalEvent($event);
        $recordHasFieldSelectValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldSelectValue);

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getPublishedRecords();

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('topic')->getSelectValue());

        # CACHE
        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getPublishedRecords();

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueSelect', get_class($records[0]->getFieldValue('topic')));

        $selectValue = $records[0]->getFieldValue('topic')->getSelectValue();

        $this->assertEquals('Cats', $selectValue->getTitle());
        $this->assertEquals('cats', $selectValue->getId());

    }




}


