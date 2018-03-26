<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryFieldController extends Controller
{

    use API1TraitLocale;

    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    /** @var Field */
    protected $field;

    protected function build(string $projectId, string $directoryId, string $fieldId, Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);
        // Check isAPIReadAllowed
        if (!$this->project->isAPIReadAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Field');
        $this->field = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldId));
        if (!$this->field) {
            throw new  NotFoundHttpException('Not found');
        }

        $this->buildLocale($request);
    }


    protected function indexData(string $projectId, string $directoryId, string $fieldId, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $fieldId, $request );
        //data


        $out = array(
            'project'   => array(
                'id'    => $this->project->getPublicId(),
                'title' => $this->project->getTitle(),
            ),
            'directory' => array(
                'id'    => $this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'field'    => array(
                'id' => $this->field->getPublicId(),
                'title' => $this->field->getTitle(),
            ),
        );

        return $out;
    }


    public function indexJSONAction(string $projectId, string $directoryId, string $fieldId, Request $request)
    {
        $response = new Response(json_encode($this->indexData($projectId, $directoryId, $fieldId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function indexJSONPAction(string $projectId, string $directoryId, string $fieldId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->indexData($projectId, $directoryId, $fieldId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    protected function selectValuesData(string $projectId, string $directoryId, string $fieldId, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $fieldId, $request );
        //data


        $out = array(
            'project'   => array(
                'id'    => $this->project->getPublicId(),
                'title' => $this->project->getTitle(),
            ),
            'directory' => array(
                'id'    => $this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'field'    => array(
                'id' => $this->field->getPublicId(),
                'title' => $this->field->getTitle(),
            ),
            'select_values' => array(),
        );

        $doctrine = $this->getDoctrine()->getManager();
        $repository = $doctrine->getRepository('DirectokiBundle:SelectValue');
        if ($this->localeMode instanceof SingleLocaleMode) {
            $selectValues = $repository->findByFieldSortForLocale($this->field, $this->localeMode->getLocale());
        } else {
            $selectValues = $repository->findBy(array('field' => $this->field));
        }

        foreach($selectValues as $selectValue) {
            if ($this->localeMode instanceof SingleLocaleMode) {
                $out['select_values'][] = array(
                    'id' => $selectValue->getPublicId(),
                    'title' => $selectValue->getCachedTitleForLocale($this->localeMode->getLocale()),
                );
            } else {
                $out['select_values'][] = array(
                    'id' => $selectValue->getPublicId(),
                    // TODO 'title' => $selectValue->getCachedTitleForLocale($this->lo),
                );

            }
        }

        return $out;
    }


    public function selectValuesJSONAction(string $projectId, string $directoryId, string $fieldId, Request $request)
    {
        $response = new Response(json_encode($this->selectValuesData($projectId, $directoryId, $fieldId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function selectValuesJSONPAction(string $projectId, string $directoryId, string $fieldId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->selectValuesData($projectId, $directoryId, $fieldId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }



}
