<?php


namespace DirectokiBundle\Tests\Action;


use DirectokiBundle\Action\ChangeFieldTypeStringToStringWithLocale;
use DirectokiBundle\Action\ChangeFieldTypeStringToText;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldChangeStringToStringWithLocaleWithDataBaseTest extends BaseTestWithDataBase {


    public function testText1() {

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

        $field1 = new Field();
        $field1->setTitle( 'Field1' );
        $field1->setPublicId( 'field1' );
        $field1->setDirectory( $directory );
        $field1->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field1->setCreationEvent( $event );
        $this->em->persist( $field1 );


        $field2 = new Field();
        $field2->setTitle( 'Field2' );
        $field2->setPublicId( 'field2' );
        $field2->setDirectory( $directory );
        $field2->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field2->setCreationEvent( $event );
        $this->em->persist( $field2 );

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);


        $recordHasFieldStringValue1 = new RecordHasFieldStringValue();
        $recordHasFieldStringValue1->setRecord( $record );
        $recordHasFieldStringValue1->setField( $field1 );
        $recordHasFieldStringValue1->setValue( 'Hairy' );
        $recordHasFieldStringValue1->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue1->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue1 );


        $recordHasFieldStringValue2 = new RecordHasFieldStringValue();
        $recordHasFieldStringValue2->setRecord( $record );
        $recordHasFieldStringValue2->setField( $field2 );
        $recordHasFieldStringValue2->setValue( 'Bikers' );
        $recordHasFieldStringValue2->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue2->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue2 );

        $this->em->flush();

        # TEST
        $field1 = $this->em->getRepository('DirectokiBundle:Field')->findOneByPublicId('field1');
        $this->assertEquals(FieldTypeString::FIELD_TYPE_INTERNAL, $field1->getFieldType());

        $field2 = $this->em->getRepository('DirectokiBundle:Field')->findOneByPublicId('field2');
        $this->assertEquals(FieldTypeString::FIELD_TYPE_INTERNAL, $field2->getFieldType());

        $stringValues = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findAll();
        $this->assertEquals(2, count($stringValues));

        $textValues = $this->em->getRepository('DirectokiBundle:RecordHasFieldTextValue')->findAll();
        $this->assertEquals(0, count($textValues));

        # CHANGE!
        $action = new ChangeFieldTypeStringToStringWithLocale($this->container);
        $action->change($field1, $locale1);

        # TEST AGAIN
        $field1 = $this->em->getRepository('DirectokiBundle:Field')->findOneByPublicId('field1');
        $this->assertEquals(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL, $field1->getFieldType());

        $field2 = $this->em->getRepository('DirectokiBundle:Field')->findOneByPublicId('field2');
        $this->assertEquals(FieldTypeString::FIELD_TYPE_INTERNAL, $field2->getFieldType());

        $stringValues = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringValue')->findAll();
        $this->assertEquals(1, count($stringValues));
        $this->assertEquals('Bikers', $stringValues[0]->getValue());

        $textValues = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringWithLocaleValue')->findAll();
        $this->assertEquals(1, count($textValues));
        $this->assertEquals('Hairy', $textValues[0]->getValue());

    }







}
