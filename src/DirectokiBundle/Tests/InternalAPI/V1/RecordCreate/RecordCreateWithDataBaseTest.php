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
class RecordCreateWithDataBaseTest extends BaseTestWithDataBase {



    public function testBlankCreate() {

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
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $field = new Field();
        $field->setTitle('Description');
        $field->setPublicId('description');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        // TODO add one of each field type here

        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        // Don't set any field values! We should be smart enough not to save.
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIDirectory->saveRecordCreate($recordCreate);
        $this->assertFalse($result->getSuccess());
        $this->assertFalse($result->isApproved());



        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->findByDirectory($directory);
        $this->assertEquals(0, count($records));


    }





}

