<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeStringWithLocaleWitdDataBaseTest extends BaseTestWithDataBase
{


    public function test1() {

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
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $record = new Record();
        $record->setDirectory( $directory );
        $record->setCreationEvent( $event );
        $record->setCachedState( RecordHasState::STATE_DRAFT );
        $record->setPublicId( 'r1' );
        $this->em->persist( $record );

        $field = new Field();
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldStringValue = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringValue->setRecord( $record );
        $recordHasFieldStringValue->setField( $field );
        $recordHasFieldStringValue->setLocale($locale1);
        $recordHasFieldStringValue->setValue( 'My Title Rocks' );
        $recordHasFieldStringValue->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue );

        $this->em->flush();

        # TEST, WE HAVE NO CACHE
        $record = $this->em->getRepository('DirectokiBundle:Record')->find($record->getId());
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $cachedValues = $fieldType->getLatestFieldValuesFromCache($field, $record);
        $this->assertEquals(0, count($cachedValues));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, WE HAVE CACHE
        $record = $this->em->getRepository('DirectokiBundle:Record')->find($record->getId());
        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $cachedValues = $fieldType->getLatestFieldValuesFromCache($field, $record);
        $this->assertEquals(2, count($cachedValues));
        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue', get_class($cachedValues[0]));
        $this->assertEquals('', $cachedValues[0]->getValue());
        $this->assertEquals('de_DE', $cachedValues[0]->getLocale()->getPublicId());
        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue', get_class($cachedValues[0]));
        $this->assertEquals('My Title Rocks', $cachedValues[1]->getValue());
        $this->assertEquals('en_GB', $cachedValues[1]->getLocale()->getPublicId());


    }

}
