<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\RecordReportEdit;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class PublishedRecordReportWithDataBaseTest extends BaseTestWithDataBase {


    public function testReport1() {

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

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $this->em->flush();

        # TEST, Nothing

        $recordReports = $this->em->getRepository('DirectokiBundle:RecordReport')->findAll();
        $this->assertEquals(0, count($recordReports));

        # REPORT

        $internalAPI = new InternalAPI($this->container);
        $internalAPIProject = $internalAPI->getProjectAPI('test1');
        $internalAPIDirectory = $internalAPIProject->getDirectoryAPI('resource');
        $internalAPIRecord = $internalAPIDirectory->getRecordAPI('r1');


        $recordReportEdit = new RecordReportEdit();
        $recordReportEdit->setDescription('Test    ');
        $recordReportEdit->setEmail('fred@example.com');

        $this->assertTrue($internalAPIRecord->saveReport($recordReportEdit)->getSuccess());


        # TEST, got report

        $recordReports = $this->em->getRepository('DirectokiBundle:RecordReport')->findAll();
        $this->assertEquals(1, count($recordReports));

        $recordReport = $recordReports[0];

        $this->assertEquals('Test', $recordReport->getDescription());
        $this->assertEquals('fred@example.com', $recordReport->getCreationEvent()->getContact()->getEmail());


    }

}

