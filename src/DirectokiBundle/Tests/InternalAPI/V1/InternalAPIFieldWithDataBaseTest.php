<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\SelectValueHasTitle;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIFieldWithDataBaseTest extends BaseTestWithDataBase
{

    public function testExceptionWhenGetSelectValuesOfStringField() {

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
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $this->em->flush();

        # TEST
        $fieldAPI = (new InternalAPI($this->container))->getProjectAPI('test1')->getDirectoryAPI('resource')->getFieldAPI('title');
        $this->expectException(\Exception::class);
        $fieldAPI->getPublishedSelectValues();


    }

    public function testGetPublishedSelectValuesMultiSelect1() {

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
        $field->setTitle( 'Tags' );
        $field->setPublicId( 'tags' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );


        $selectValue1 = new SelectValue();
        $selectValue1->setField($field);
        $selectValue1->setCreationEvent($event);
        $this->em->persist($selectValue1);
        // Need to flush after each one to avoid risk that randomly 2 of the selectValues will be given same public Id!
        $this->em->flush();

        $selectValueHasTitle1 = new SelectValueHasTitle();
        $selectValueHasTitle1->setSelectValue($selectValue1);
        $selectValueHasTitle1->setLocale($locale);
        $selectValueHasTitle1->setCreationEvent($event);
        $selectValueHasTitle1->setTitle('PHP');
        $this->em->persist($selectValueHasTitle1);


        $selectValue2 = new SelectValue();
        $selectValue2->setField($field);
        $selectValue2->setCreationEvent($event);
        $this->em->persist($selectValue2);
        $this->em->flush();
        
        
        $selectValueHasTitle2 = new SelectValueHasTitle();
        $selectValueHasTitle2->setSelectValue($selectValue2);
        $selectValueHasTitle2->setLocale($locale);
        $selectValueHasTitle2->setCreationEvent($event);
        $selectValueHasTitle2->setTitle('Symfony');
        $this->em->persist($selectValueHasTitle2);


        $selectValue3 = new SelectValue();
        $selectValue3->setField($field);
        $selectValue3->setCreationEvent($event);
        $this->em->persist($selectValue3);
        $this->em->flush();


        $selectValueHasTitle3 = new SelectValueHasTitle();
        $selectValueHasTitle3->setSelectValue($selectValue3);
        $selectValueHasTitle3->setLocale($locale);
        $selectValueHasTitle3->setCreationEvent($event);
        $selectValueHasTitle3->setTitle('Postgresql');
        $this->em->persist($selectValueHasTitle3);

        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);

        # TEST
        $selectValues = (new InternalAPI($this->container))->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValues();

        $this->assertEquals(3, count($selectValues));

        $this->assertEquals('PHP', $selectValues[0]->getTitle());
        $this->assertEquals('Symfony', $selectValues[1]->getTitle());
        $this->assertEquals('Postgresql', $selectValues[2]->getTitle());


    }


    public function testGetPublishedSelectValueMultiSelect1() {

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
        $field->setTitle( 'Tags' );
        $field->setPublicId( 'tags' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );


        $selectValue1 = new SelectValue();
        $selectValue1->setField($field);
        $selectValue1->setCreationEvent($event);
        $selectValue1->setPublicId('php');
        $this->em->persist($selectValue1);

        $selectValueHasTitle1 = new SelectValueHasTitle();
        $selectValueHasTitle1->setSelectValue($selectValue1);
        $selectValueHasTitle1->setLocale($locale);
        $selectValueHasTitle1->setCreationEvent($event);
        $selectValueHasTitle1->setTitle('PHP');
        $this->em->persist($selectValueHasTitle1);


        $this->em->flush();

        $action = new UpdateFieldCache($this->container);
        $action->runForField($field);


        $this->em->flush();

        # TEST
        $selectValue = (new InternalAPI($this->container))->getProjectAPI('test1')->setSingleLocaleModeByPublicId('en_GB')->getDirectoryAPI('resource')->getFieldAPI('tags')->getPublishedSelectValue('php');

        $this->assertEquals('PHP', $selectValue->getTitle());


    }




}


