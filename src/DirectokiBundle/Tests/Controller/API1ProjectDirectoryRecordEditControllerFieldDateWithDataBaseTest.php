<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldDateValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeDate;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditControllerFieldDateWithDataBaseTest extends BaseTestWithDataBase {


    /** If call doesn't even pass a field, nothing should change and no new records created */
    function testDateNoFieldPassed() {


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

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # TEST

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(0, count($values));

        /**
         *
         * TODO this should work but caused issues. If we could use this, these tests will cover even more ground!
        $fieldType = new FieldTypeDate($this->container);
         * $latestValue = $fieldType->getLatestFieldValue($field, $record);
        $this->assertEquals(null, $latestValue->getValue());

        $recordsToModerate = $fieldType->getFieldValuesToModerate($field, $record);
        $this->assertEquals(0, count($recordsToModerate));
        **/

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'comment' => 'I send a comment but no fields with it.',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(0, count($values));

    }


    /** If call passes a field when previously there was none, there should be new records created */
    function testDatePassChangeNoExisting() {


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


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_date_value' => '2017-01-01',
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("2017-01-01", $value->getValue()->format('Y-m-d'));
    }

    /** If field has no values, and a empty Date passed, nothing should happen, no new records. */
    function testDatePassSameNoExisting() {


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


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_date_value' => '',
            'comment' => 'I have no idea what to say',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(0, count($values));

    }

    /** If field has value, but new value passed, we should have new records */
    function testDatePassChangeWithExisting() {


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


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);



        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setApprovedAt(new \DateTime());
        $recordHasFieldDateValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(1, count($values));

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(0, count($contacts));


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_date_value' => '2017-01-01',
            'comment' => 'Next Year',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN



        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(2, count($values));

        $value = $values[0];
        $this->assertEquals("2016-01-01", $value->getValue()->format('Y-m-d'));

        $value = $values[1];
        $this->assertEquals("2017-01-01", $value->getValue()->format('Y-m-d'));

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(1, count($contacts));
        $this->assertEquals('user1@example.com', $contacts[0]->getEmail());

    }


    /** If field has a value, and call is made with same value, nothing should happen! */
    function testDatePassSameWithExisting() {


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


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setApprovedAt(new \DateTime());
        $recordHasFieldDateValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(1, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_date_value' => '2016-01-01',
            'comment' => 'I just want my name on this, but I made no change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN



        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(1, count($values));


    }





    /** If field has value, but null value passed, we should have new records */
    function testDatePassNullWithExisting() {


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


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);



        $recordHasFieldDateValue = new RecordHasFieldDateValue();
        $recordHasFieldDateValue->setRecord($record);
        $recordHasFieldDateValue->setField($field);
        $recordHasFieldDateValue->setValue(new \DateTime('2016-01-01'));
        $recordHasFieldDateValue->setApprovedAt(new \DateTime());
        $recordHasFieldDateValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldDateValue);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(1, count($values));

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(0, count($contacts));


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_date_value' => '',
            'comment' => 'No Date',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN



        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldDateValue')->findAll();
        $this->assertEquals(2, count($values));

        $value = $values[0];
        $this->assertEquals("2016-01-01", $value->getValue()->format('Y-m-d'));

        $value = $values[1];
        $this->assertNull($value->getValue());

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(1, count($contacts));
        $this->assertEquals('user1@example.com', $contacts[0]->getEmail());

    }




}

