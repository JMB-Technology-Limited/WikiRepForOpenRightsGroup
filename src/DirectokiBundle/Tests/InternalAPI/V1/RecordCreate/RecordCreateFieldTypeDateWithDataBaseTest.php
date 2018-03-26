<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\RecordCreate;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
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
class RecordCreateFieldTypeDateWithDataBaseTest extends BaseTestWithDataBase {

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

        $field = new Field();
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('date')->setNewValue(new \DateTime('2016-01-01'));
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');

        $result = $internalAPIDirectory->saveRecordCreate($recordCreate);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertFalse($result->isApproved());



         # TEST

        $record = $this->em->getRepository('DirectokiBundle:Record')->findOneBy(array());

        $this->assertTrue($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $record);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldDateValue', get_class($fieldModerationNeeded));
        $this->assertEquals('2016-01-01', $fieldModerationNeeded->getValue()->format('Y-m-d'));


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

        $field = new Field();
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('date')->setNewValue(new \DateTime('2016-01-01'));
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(true);

        $result = $internalAPIDirectory->saveRecordCreate($recordCreate);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertFalse($result->isApproved());




        # TEST


        $record = $this->em->getRepository('DirectokiBundle:Record')->findOneBy(array());

        $this->assertTrue($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $record);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldDateValue', get_class($fieldModerationNeeded));
        $this->assertEquals('2016-01-01', $fieldModerationNeeded->getValue()->format('Y-m-d'));


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

        $field = new Field();
        $field->setTitle('Date');
        $field->setPublicId('date');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('date')->setNewValue(new \DateTime('2016-01-01'));
        $recordCreate->setComment('Test');
        $recordCreate->setUser($user);
        $recordCreate->setApproveInstantlyIfAllowed(true);

        $result = $internalAPIDirectory->saveRecordCreate($recordCreate);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->hasFieldErrors());
        $this->assertTrue($result->isApproved());
        $this->assertTrue(is_string($result->getId()));




        # TEST


        $record = $this->em->getRepository('DirectokiBundle:Record')->findOneBy(array());

        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

        $records = $this->em->getRepository('DirectokiBundle:Record')->findByDirectory($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $this->assertEquals('2016-01-01', $fieldType->getLatestFieldValues($field, $records[0])[0]->getValue()->format('Y-m-d'));



    }


}

