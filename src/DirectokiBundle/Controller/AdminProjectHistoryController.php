<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectHistoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Event */
    protected $event;

    protected function build(string $projectId, string $historyId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Locale');
        $this->locale = $repository->findOneByProject($this->project);
        // TODO load by user input, not just selecting one at random!
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Event');
        $this->event = $repository->findOneBy(array('project'=>$this->project, 'id'=>$historyId));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction(string $projectId, string $historyId)
    {

        // build
        $this->build($projectId, $historyId);

        //data

        $doctrine = $this->getDoctrine()->getManager();
        $recordRepo = $doctrine->getRepository('DirectokiBundle:Record');
        $directoryRepo = $doctrine->getRepository('DirectokiBundle:Directory');
        $fieldRepo = $doctrine->getRepository('DirectokiBundle:Field');
        $localeRepo = $doctrine->getRepository('DirectokiBundle:Locale');
        $selectValuesRepo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        $recordHasStateRepo = $doctrine->getRepository('DirectokiBundle:RecordHasState');
        $recordReportRepo = $doctrine->getRepository('DirectokiBundle:RecordReport');


        $recordsCreated = $recordRepo->findBy(array('creationEvent'=>$this->event));
        $directoriesCreated = $directoryRepo->findBy(array('creationEvent'=>$this->event));
        $fieldsCreated = $fieldRepo->findBy(array('creationEvent'=>$this->event));
        $localesCreated = $localeRepo->findBy(array('creationEvent'=>$this->event));
        $selectValuesCreated = $selectValuesRepo->findBy(array('creationEvent'=>$this->event));
        $recordStates = $recordHasStateRepo->findByEvent($this->event);
        $recordReportsCreated = $recordReportRepo->findByCreationEvent($this->event);
        $recordReportsResolved = $recordReportRepo->findByResolutionEvent($this->event);

        return $this->render('DirectokiBundle:AdminProjectHistory:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'event' => $this->event,
            'recordsCreated' => $recordsCreated,
            'directoriesCreated' => $directoriesCreated,
            'fieldsCreated' => $fieldsCreated,
            'localesCreated' => $localesCreated,
            'selectValuesCreated' => $selectValuesCreated,
            'recordStates' => $recordStates,
            'reportsCreated' => $recordReportsCreated,
            'reportsResolved' => $recordReportsResolved,
        ));

    }




}
