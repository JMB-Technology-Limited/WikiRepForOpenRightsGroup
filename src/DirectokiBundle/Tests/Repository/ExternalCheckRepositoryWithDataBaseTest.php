<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\ExternalCheck;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldBooleanValue;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\RecordReport;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ExternalCheckRepositoryWithDataBaseTest extends BaseTestWithDataBase
{

    function testWasURLCheckedRecentlyFail1() {

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

        $this->em->flush();

        # TEST
        $this->assertFalse($this->em->getRepository('DirectokiBundle:ExternalCheck')->wasURLCheckedRecently("http://google.com", $project));
    }

    function testWasURLCheckedRecentlyFail2() {

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

        // This check was so long ago it doesn't count.
        $externalCheck = new ExternalCheck();
        $externalCheck->setProject($project);
        $externalCheck->setCreatedAt(new \DateTime('2010-01-01 10:00:00'));
        $externalCheck->setErrorMessage('');
        $externalCheck->setHttpResponseCode(200);
        $externalCheck->setUrl("http://google.com");
        $this->em->persist($externalCheck);

        $this->em->flush();

        # TEST
        $this->assertFalse($this->em->getRepository('DirectokiBundle:ExternalCheck')->wasURLCheckedRecently("http://google.com", $project));
    }

    function testWasURLCheckedRecentlyFail3() {

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

        // This check is for a different domain
        $externalCheck = new ExternalCheck();
        $externalCheck->setProject($project);
        $externalCheck->setCreatedAt(new \DateTime());
        $externalCheck->setErrorMessage('');
        $externalCheck->setHttpResponseCode(200);
        $externalCheck->setUrl("http://google.co.uk");
        $this->em->persist($externalCheck);

        $this->em->flush();

        # TEST
        $this->assertFalse($this->em->getRepository('DirectokiBundle:ExternalCheck')->wasURLCheckedRecently("http://google.com", $project));
    }

    function testWasURLCheckedRecentlyPass1() {

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

        $externalCheck = new ExternalCheck();
        $externalCheck->setProject($project);
        $externalCheck->setCreatedAt(new \DateTime());
        $externalCheck->setErrorMessage('');
        $externalCheck->setHttpResponseCode(200);
        $externalCheck->setUrl("http://google.com");
        $this->em->persist($externalCheck);

        $this->em->flush();

        # TEST
        $this->assertTrue($this->em->getRepository('DirectokiBundle:ExternalCheck')->wasURLCheckedRecently("http://google.com", $project));
    }

}

