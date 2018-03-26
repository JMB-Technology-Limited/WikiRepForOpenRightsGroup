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
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\LocaleMode\MultiLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordControllerFieldStringWithLocaleWithDataBaseTest extends BaseTestWithDataBase
{


    function testStringSingleLocaleMode() {


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

        $locale1 = new Locale();
        $locale1->setTitle('en_GB');
        $locale1->setPublicId('en_GB');
        $locale1->setProject($project);
        $locale1->setCreationEvent($event);
        $this->em->persist($locale1);

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
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasFieldStringWithLocaleValue = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue->setRecord($record);
        $recordHasFieldStringWithLocaleValue->setField($field);
        $recordHasFieldStringWithLocaleValue->setLocale($locale1);
        $recordHasFieldStringWithLocaleValue->setValue('My Title Rocks');
        $recordHasFieldStringWithLocaleValue->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $this->em->flush();

        # DIRECT CALL NO CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new SingleLocaleMode($locale1), false);
        $this->assertEquals('My Title Rocks', $data['value']);

        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # DIRECT CALL WITH CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new SingleLocaleMode($locale1), true);
        $this->assertEquals('My Title Rocks', $data['value']);

        # CALL API, TEST
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locale=en_GB');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());

        $this->assertEquals('My Title Rocks',$data->fields->title->value->value);
        $this->assertFalse(isset($data->fields->title->value->value_en_GB));


    }

    function testStringMultiLocaleModeOneLocale() {


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

        $locale1 = new Locale();
        $locale1->setTitle('en_GB');
        $locale1->setPublicId('en_GB');
        $locale1->setProject($project);
        $locale1->setCreationEvent($event);
        $this->em->persist($locale1);

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
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasFieldStringWithLocaleValue = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue->setRecord($record);
        $recordHasFieldStringWithLocaleValue->setField($field);
        $recordHasFieldStringWithLocaleValue->setLocale($locale1);
        $recordHasFieldStringWithLocaleValue->setValue('My Title Rocks');
        $recordHasFieldStringWithLocaleValue->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $this->em->flush();


        # DIRECT CALL NO CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new MultiLocaleMode(array($locale1)), false);
        $this->assertEquals('My Title Rocks', $data['value_en_GB']);

        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # DIRECT CALL WITH CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new MultiLocaleMode(array($locale1)), true);
        $this->assertEquals('My Title Rocks', $data['value_en_GB']);




        # CALL API, TEST
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locales=en_GB');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());

        $this->assertEquals('My Title Rocks',$data->fields->title->value->value_en_GB);
        $this->assertFalse(isset($data->fields->title->value->value));


    }

    function testStringMultiLocaleModeTwoLocales() {


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

        $locale1 = new Locale();
        $locale1->setTitle('en_GB');
        $locale1->setPublicId('en_GB');
        $locale1->setProject($project);
        $locale1->setCreationEvent($event);
        $this->em->persist($locale1);

        $locale2 = new Locale();
        $locale2->setTitle('de_DE');
        $locale2->setPublicId('de_DE');
        $locale2->setProject($project);
        $locale2->setCreationEvent($event);
        $this->em->persist($locale2);

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
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasFieldStringWithLocaleValue1 = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue1->setRecord($record);
        $recordHasFieldStringWithLocaleValue1->setField($field);
        $recordHasFieldStringWithLocaleValue1->setLocale($locale1);
        $recordHasFieldStringWithLocaleValue1->setValue('My Title Rocks');
        $recordHasFieldStringWithLocaleValue1->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue1->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue1);

        $recordHasFieldStringWithLocaleValue2 = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue2->setRecord($record);
        $recordHasFieldStringWithLocaleValue2->setField($field);
        $recordHasFieldStringWithLocaleValue2->setLocale($locale2);
        $recordHasFieldStringWithLocaleValue2->setValue('Mein Titel ist schön');
        $recordHasFieldStringWithLocaleValue2->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue2->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue2);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $this->em->flush();


        # DIRECT CALL NO CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new MultiLocaleMode(array($locale1, $locale2)), false);
        $this->assertEquals('My Title Rocks', $data['value_en_GB']);
        $this->assertEquals('Mein Titel ist schön', $data['value_de_DE']);

        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # DIRECT CALL WITH CACHE, TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $data = $fieldType->getAPIJSON($field, $record, new MultiLocaleMode(array($locale1, $locale2)), true);
        $this->assertEquals('My Title Rocks', $data['value_en_GB']);
        $this->assertEquals('Mein Titel ist schön', $data['value_de_DE']);




        # CALL API, TEST
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locales=en_GB,de_DE');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());

        $this->assertEquals('My Title Rocks',$data->fields->title->value->value_en_GB);
        $this->assertEquals('Mein Titel ist schön',$data->fields->title->value->value_de_DE);
        $this->assertFalse(isset($data->fields->title->value->value));


    }


    function testStringMultiLocaleModeAllLocales() {


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

        $locale1 = new Locale();
        $locale1->setTitle('en_GB');
        $locale1->setPublicId('en_GB');
        $locale1->setProject($project);
        $locale1->setCreationEvent($event);
        $this->em->persist($locale1);

        $locale2 = new Locale();
        $locale2->setTitle('de_DE');
        $locale2->setPublicId('de_DE');
        $locale2->setProject($project);
        $locale2->setCreationEvent($event);
        $this->em->persist($locale2);

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
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasFieldStringWithLocaleValue1 = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue1->setRecord($record);
        $recordHasFieldStringWithLocaleValue1->setField($field);
        $recordHasFieldStringWithLocaleValue1->setLocale($locale1);
        $recordHasFieldStringWithLocaleValue1->setValue('My Title Rocks');
        $recordHasFieldStringWithLocaleValue1->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue1->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue1);

        $recordHasFieldStringWithLocaleValue2 = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue2->setRecord($record);
        $recordHasFieldStringWithLocaleValue2->setField($field);
        $recordHasFieldStringWithLocaleValue2->setLocale($locale2);
        $recordHasFieldStringWithLocaleValue2->setValue('Mein Titel ist schön');
        $recordHasFieldStringWithLocaleValue2->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue2->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue2);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $this->em->flush();

        # DIRECT CALL NO CACHE, TEST
        # Actually this is the same test as in testStringMultiLocaleModeTwoLocales() so we won't bother

        # CACHE
        # Actually this is the same test as in testStringMultiLocaleModeTwoLocales() so we won't bother

        # DIRECT CALL WITH CACHE, TEST
        # Actually this is the same test as in testStringMultiLocaleModeTwoLocales() so we won't bother

        # CALL API, TEST
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/index.json?locales=*');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());

        $this->assertEquals('My Title Rocks',$data->fields->title->value->value_en_GB);
        $this->assertEquals('Mein Titel ist schön',$data->fields->title->value->value_de_DE);
        $this->assertFalse(isset($data->fields->title->value->value));


    }


}

