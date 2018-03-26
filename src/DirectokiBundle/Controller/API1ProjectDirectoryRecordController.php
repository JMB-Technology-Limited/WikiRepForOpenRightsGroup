<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\StringFieldType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordController extends Controller
{

    use API1TraitLocale;

    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    /** @var Record */
    protected $record;

    protected function build(string $projectId, string $directoryId, string $recordId, Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        // Check isAPIReadAllowed
        if (!$this->project->isAPIReadAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Record');
        $this->record = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$recordId));
        if (!$this->record) {
            throw new  NotFoundHttpException('Not found');
        }

        $this->buildLocale($request);
    }


    protected function indexData(string $projectId, string $directoryId, string $recordId, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $recordId, $request );
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $state = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);

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
            'record'    => array(
                'id' => $this->record->getPublicId(),
                'published' => $state->isStatePublished(),
            ),
            'fields'    => array(),
        );

        if ($state->isStatePublished()) {
            $out['fields'] = array();
            $fields        = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

            foreach ( $fields as $field ) {

                $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

                $out['fields'][ $field->getPublicId() ] = array(
                    'id'    => $field->getPublicId(),
                    'type'  => $fieldType::FIELD_TYPE_API1,
                    'title' => $field->getTitle(),
                    'value' => $fieldType->getAPIJSON( $field, $this->record ,  $this->localeMode, false),
                );

            }
        }

        return $out;
    }


    public function indexJSONAction(string $projectId, string $directoryId, string $recordId, Request $request)
    {
        $response = new Response(json_encode($this->indexData($projectId, $directoryId, $recordId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function indexJSONPAction(string $projectId, string $directoryId, string $recordId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->indexData($projectId, $directoryId, $recordId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }



}
