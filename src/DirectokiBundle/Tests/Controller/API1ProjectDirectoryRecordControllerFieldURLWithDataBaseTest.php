<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldURLValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeURL;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordControllerFieldURLWithDataBaseTest extends BaseTestWithDataBase {


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
        $field->setTitle('URL');
        $field->setPublicId('url');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeURL::FIELD_TYPE_INTERNAL);
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

        $recordHasFieldURLValue = new RecordHasFieldURLValue();
        $recordHasFieldURLValue->setRecord($record);
        $recordHasFieldURLValue->setField($field);
        $recordHasFieldURLValue->setValue('http://www.directoki.org/');
        $recordHasFieldURLValue->setApprovedAt(new \DateTime());
        $recordHasFieldURLValue->setCreationEvent($event);
        $recordHasFieldURLValue->setApprovalEvent($event);
        $this->em->persist($recordHasFieldURLValue);

        $this->em->flush();

        # CACHE

        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST

        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locale=en');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);


        $this->assertEquals('http://www.directoki.org/', $data['fields']['url']['value']['value']);


    }

}

