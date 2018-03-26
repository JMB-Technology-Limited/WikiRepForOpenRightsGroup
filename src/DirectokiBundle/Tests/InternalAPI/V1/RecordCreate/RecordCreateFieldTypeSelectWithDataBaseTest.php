<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\RecordCreate;


use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeEmail;
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
class RecordCreateFieldTypeSelectWithDataBaseTest extends BaseTestWithDataBase
{


    public function testMultiSelectField() {

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

        $field = new Field();
        $field->setTitle('Tag');
        $field->setPublicId('tag');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeSelect::FIELD_TYPE_INTERNAL);
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

        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource');

        $selectValuesFromAPI = $internalAPIDirectory->getFieldAPI('tag')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());


        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('tag')->setNewValue($selectValuesFromAPI[0]);
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIDirectory->saveRecordCreate($recordCreate);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->isApproved());
        $this->assertFalse($result->hasFieldErrors());


        # TEST

        $record = $this->em->getRepository('DirectokiBundle:Record')->findOneBy(array());

        $this->assertTrue($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $record);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldValue', get_class($fieldModerationNeeded));
        $this->assertEquals('PHP', $fieldModerationNeeded->getFieldValue()->getSelectValue()->getCachedTitleForLocale($locale));


    }


}
