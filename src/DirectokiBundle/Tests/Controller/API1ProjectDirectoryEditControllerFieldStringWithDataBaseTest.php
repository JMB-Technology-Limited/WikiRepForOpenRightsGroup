<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryEditControllerFieldStringWithDataBaseTest extends BaseTestWithDataBase {


    function testNewWithNoFieldsPassed() {


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
        $field->setCreationEvent($event);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $this->em->persist($field);


        $this->em->flush();


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/newRecord.json', array(
            'comment' => 'I send a comment but no fields with it.',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(true, $response['success']);

        # TEST

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findAll();
        $this->assertEquals(0, count($values));

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(0, count($contacts));

    }


    function testNewWithData() {


        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $project->setAPIModeratedEditAllowed(true);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);


        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setCreationEvent($event);
        $directory->setProject($project);
        $this->em->persist($directory);

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setCreationEvent($event);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $this->em->persist($field);


        $this->em->flush();


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/newRecord.json', array(
            'field_title_value' => 'TITLE MINE',
            'comment' => 'I send a comment but no fields with it.',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(true, $response['success']);

        # TEST

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findAll();
        $this->assertEquals(1, count($values));

        $contacts = $this->em->getRepository('DirectokiBundle:Contact')->findAll();
        $this->assertEquals(1, count($contacts));
        $this->assertEquals('user1@example.com', $contacts[0]->getEmail());

    }


    function testNewWithDataWhenAccessDenied() {


        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $project->setAPIModeratedEditAllowed(false);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);


        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setCreationEvent($event);
        $directory->setProject($project);
        $this->em->persist($directory);

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setCreationEvent($event);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $this->em->persist($field);


        $this->em->flush();


        # TEST
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/newRecord.json', array(
            'field_title_value' => 'TITLE MINE',
            'comment' => 'I send a comment but no fields with it.',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(false, $response['success']);
    }




}

