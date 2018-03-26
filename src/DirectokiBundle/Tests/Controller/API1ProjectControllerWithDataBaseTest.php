<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectControllerWithDataBaseTest extends BaseTestWithDataBase {


    function testGetLocales() {


        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $project->setAPIReadAllowed(true);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $locale = new Locale();
        $locale->setProject($project);
        $locale->setCreationEvent($event);
        $locale->setPublicId('en_GB');
        $locale->setTitle('en_GB');
        $this->em->persist($locale);

        $this->em->flush();

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/locales.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, count($data['locales']));
        $this->assertEquals('en_GB', $data['locales'][0]['id']);
        $this->assertEquals('en_GB', $data['locales'][0]['id']);

    }




}

