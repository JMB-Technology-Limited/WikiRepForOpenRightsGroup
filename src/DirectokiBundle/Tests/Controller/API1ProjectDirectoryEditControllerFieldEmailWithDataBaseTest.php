<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryEditControllerFieldEmailWithDataBaseTest extends BaseTestWithDataBase {




    function testNewWithNotAnEmail() {


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
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setCreationEvent($event);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $this->em->persist($field);


        $this->em->flush();


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/newRecord.json', array(
            'field_email_value' => 'I dont know the email.',
            'comment' => 'I add email info',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());


        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Please set a valid email!',  $response['field_errors']['email'][0]);

    }

    function testNewWithNotAnEmailAskFor200() {


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
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setCreationEvent($event);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $this->em->persist($field);


        $this->em->flush();


        # CALL API
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/newRecord.jsonp', array(
            'field_email_value' => 'I dont know the email.',
            'comment' => 'I add email info',
            'email' => 'user1@example.com',
            'errorAs200' => 1,
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($this->getJSONBitOfAJSONPString($client->getResponse()->getContent()), true);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Please set a valid email!',  $response['field_errors']['email'][0]);

    }



}

