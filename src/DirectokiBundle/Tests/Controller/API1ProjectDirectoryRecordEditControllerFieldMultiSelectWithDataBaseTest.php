<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldURLValue;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\SelectValueHasTitle;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditControllerFieldMultiSelectWithDataBaseTest extends BaseTestWithDataBase {


    function testURLPassOneTitleValueNoExisting() {


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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json?locale=en_GB', array(
            'field_tech_add_title' => 'php ', // passing different case and extra space on purpose
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getCachedTitleForLocale($locale));
    }


    function testURLPassOneIdValueNoExisting() {


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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json?locale=en_GB', array(
            'field_tech_add_id' => $selectValue->getPublicId(),
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getCachedTitleForLocale($locale));
    }




    function testURLRemovePassOneID() {


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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $selectValueHasTitle = new SelectValueHasTitle();
        $selectValueHasTitle->setSelectValue($selectValue);
        $selectValueHasTitle->setLocale($locale);
        $selectValueHasTitle->setCreationEvent($event);
        $selectValueHasTitle->setTitle('PHP');
        $this->em->persist($selectValueHasTitle);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasMultiSelectValue = new RecordHasFieldMultiSelectValue();
        $recordHasMultiSelectValue->setRecord($record);
        $recordHasMultiSelectValue->setField($field);
        $recordHasMultiSelectValue->setSelectValue($selectValue);
        $recordHasMultiSelectValue->setAdditionCreationEvent($event);
        $recordHasMultiSelectValue->setAdditionApprovedAt(new \DateTime());
        $recordHasMultiSelectValue->setAdditionApprovalEvent($event);
        $this->em->persist($recordHasMultiSelectValue);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getCachedTitleForLocale($locale));
        $this->assertNull($value->getRemovalCreatedAt());

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json?locale=en_GB', array(
            'field_tech_remove_id' => $selectValue->getPublicId(),
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getCachedTitleForLocale($locale));
        $this->assertNotNull($value->getRemovalCreatedAt());

    }

    function testURLPassTwoIdValuesCommaSeperatedNoExisting() {


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
        $locale->setProject($project);
        $locale->setTitle('en_GB');
        $locale->setPublicId('en_GB');
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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue1 = new SelectValue();
        $selectValue1->setField($field);
        $selectValue1->setCreationEvent($event);
        $this->em->persist($selectValue1);

        $selectValue1HasTitle = new SelectValueHasTitle();
        $selectValue1HasTitle->setSelectValue($selectValue1);
        $selectValue1HasTitle->setLocale($locale);
        $selectValue1HasTitle->setCreationEvent($event);
        $selectValue1HasTitle->setTitle('PHP');
        $this->em->persist($selectValue1HasTitle);

        $selectValue2 = new SelectValue();
        $selectValue2->setField($field);
        $selectValue2->setCreationEvent($event);
        $this->em->persist($selectValue2);

        $selectValue2HasTitle = new SelectValueHasTitle();
        $selectValue2HasTitle->setSelectValue($selectValue2);
        $selectValue2HasTitle->setLocale($locale);
        $selectValue2HasTitle->setCreationEvent($event);
        $selectValue2HasTitle->setTitle('Symfony');
        $this->em->persist($selectValue2HasTitle);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json?locale=en_GB', array(
            'field_tech_add_id' => $selectValue1->getPublicId().','.$selectValue2->getPublicId(),
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(true, $data['success']);

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(2, count($values));

        # TODO the order these come out might be arbitary and to make the test more robust I do something about checking that.

        $this->assertEquals("PHP", $values[0]->getSelectValue()->getCachedTitleForLocale($locale));
        $this->assertEquals("Symfony", $values[1]->getSelectValue()->getCachedTitleForLocale($locale));
    }


}

