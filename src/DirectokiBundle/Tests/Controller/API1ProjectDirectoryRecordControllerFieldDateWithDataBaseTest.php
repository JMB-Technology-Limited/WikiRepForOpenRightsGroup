<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldDateValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeDate;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordControllerFieldDateWithDataBaseTest extends BaseTestWithDataBase {


    function testWithLocale() {

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
        $locale->setPublicId('en');
        $locale->setProject($project);
        $locale->setTitle('EN');
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
        $field->setTitle('Start');
        $field->setPublicId('start');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeDate::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2017-03-02 12:01:00', new \DateTimeZone('UTC')));
        $recordHasFieldDateValue->setApprovedAt(new \DateTime());
        $recordHasFieldDateValue->setCreationEvent($event);
        $recordHasFieldDateValue->setApprovalEvent($event);

        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # CACHE

        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST

        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locale=en');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);


        $this->assertEquals(2017, $data['fields']['start']['value']['year']);
        $this->assertEquals(3, $data['fields']['start']['value']['month']);
        $this->assertEquals(2, $data['fields']['start']['value']['day']);


    }

}

