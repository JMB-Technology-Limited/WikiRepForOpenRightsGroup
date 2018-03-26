<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\RecordsInDirectoryQuery;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleDirectoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Directory */
    protected $directory;

    protected function build(string $projectId, string $localeId, string $directoryId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->project->isWebReadAllowed()) {
            throw new NotFoundHttpException('Read Feature Not Found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Locale');
        $this->locale = $repository->findOneBy(array('project'=>$this->project,'publicId'=>$localeId));
        if (!$this->locale) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction(string $projectId, string $localeId, string $directoryId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        return $this->render('DirectokiBundle:ProjectLocaleDirectory:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
        ));

    }

    public function recordsAction(string $projectId, string $localeId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);
        $field = count($fields) > 0 ? $fields[0] : null;
        if ($field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $fieldTemplate = $fieldType->getViewTemplate($field);
            $fieldIsMultiple = $fieldType->isMultipleType($field);
        } else {
            $fieldType = null;
            $fieldTemplate = null;
            $fieldIsMultiple = null;
        }


        $recordSearchQuery = new RecordsInDirectoryQuery($this->directory, $this->locale);
        $recordSearchQuery->setPublishedOnly(true);
        if ($request->query->get('search')) {
            $recordSearchQuery->setFullTextSearch($request->query->get('search'));
        }

        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->findByRecordsInDirectoryQuery($recordSearchQuery);
        return $this->render('DirectokiBundle:ProjectLocaleDirectory:records.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'records' => $records,
            'field' => $field,
            'fieldType' => $fieldType,
            'fieldTemplate' => $fieldTemplate,
            'fieldIsMultilple' => $fieldIsMultiple,
            'search' => $recordSearchQuery->getFullTextSearch(),
        ));

    }

}
