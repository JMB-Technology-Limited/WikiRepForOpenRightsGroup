<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeTextWithDataBaseTest extends BaseTestWithDataBase
{


    public function testLineEndings1() {

        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $record = new Record();
        $record->setDirectory( $directory );
        $record->setCreationEvent( $event );
        $record->setCachedState( RecordHasState::STATE_PUBLISHED );
        $record->setPublicId( 'r1' );
        $this->em->persist( $record );

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle( 'Description' );
        $field->setPublicId( 'description' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeText::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldTextValue = new RecordHasFieldTextValue();
        $recordHasFieldTextValue->setRecord( $record );
        $recordHasFieldTextValue->setField( $field );
        $recordHasFieldTextValue->setValue( "123\n456"  );
        $recordHasFieldTextValue->setApprovedAt( new \DateTime() );
        $recordHasFieldTextValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldTextValue );

        $this->em->flush();

        # TEST, WE HAVE NO MODS


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));


        # EDIT - Same value, but we try and insert a \r. And it should be rejected.
        $internalAPI = new InternalAPI($this->container);
        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');

        $recordEditIntAPI = $internalAPIDirectory->getPublishedEdit();
        $recordEditIntAPI->getFieldValueEdit('description')->setNewValue("123\r\n456");
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');

        $this->assertFalse($internalAPIDirectory->savePublishedEdit($recordEditIntAPI)->getSuccess());


        # TEST, WE *STILL* HAVE NO MODS


        $this->assertFalse($this->em->getRepository('DirectokiBundle:Record')->doesRecordNeedAdminAttention($record));

    }

    public function testGetURLsForExternalCheck1() {

        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $record = new Record();
        $record->setDirectory( $directory );
        $record->setCreationEvent( $event );
        $record->setCachedState( RecordHasState::STATE_PUBLISHED );
        $record->setPublicId( 'r1' );
        $this->em->persist( $record );

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle( 'Description' );
        $field->setPublicId( 'description' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeText::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldTextValue = new RecordHasFieldTextValue();
        $recordHasFieldTextValue->setRecord( $record );
        $recordHasFieldTextValue->setField( $field );
        $recordHasFieldTextValue->setValue( "123\n456\nhttp://www.directoki.org/\nhttps://github.com/Directoki/Directoki-Core For more!"  );
        $recordHasFieldTextValue->setApprovedAt( new \DateTime() );
        $recordHasFieldTextValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldTextValue );

        $this->em->flush();

        # TEST
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $urls = $fieldType->getURLsForExternalCheck($field, $record);
        $this->assertEquals(2, count($urls));
        $this->assertEquals('http://www.directoki.org/', $urls[0]);
        $this->assertEquals('https://github.com/Directoki/Directoki-Core', $urls[1]);

    }

}
