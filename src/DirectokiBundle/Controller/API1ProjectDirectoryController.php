<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordHasState;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryController extends Controller
{

    use API1TraitLocale;

    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    protected function build(string $projectId, string $directoryId, Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        // check isAPIReadAllowed
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

        $this->buildLocale($request);
    }


    public function indexJSONAction(string $projectId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $directoryId, $request);
        //data
        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            )
        );

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;


    }

    protected  function fieldsData(string $projectId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $directoryId, $request);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Field');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'fields'=>array()
        );

        foreach($repo->findByDirectory($this->directory) as $field) {
            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );
            $out['fields'][$field->getPublicId()] = array(
                'id'=>$field->getPublicId(),
                'title'=>$field->getTitle(),
                'type'  => $fieldType::FIELD_TYPE_API1,
            );
        }

        return $out;

    }

    public function fieldsJSONAction(string $projectId, string $directoryId, Request $request)
    {
        $response = new Response(json_encode($this->fieldsData($projectId, $directoryId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function fieldsJSONPAction(string $projectId, string $directoryId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->fieldsData($projectId, $directoryId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    public function recordsJSONAction(string $projectId, string $directoryId, Request $request)
    {

        // build
        $this->build($projectId, $directoryId, $request);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'records'=>array()
        );

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);

        foreach($repo->findByDirectory($this->directory) as $record) {
            if ($record->getCachedState() == RecordHasState::STATE_PUBLISHED) {

                $fieldData = array();

                foreach($fields as $field) {
                    $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

                    $fieldData[ $field->getPublicId() ] = array(
                        'id'    => $field->getPublicId(),
                        'type'  => $fieldType::FIELD_TYPE_API1,
                        'title' => $field->getTitle(),
                        'value' => $fieldType->getAPIJSON( $field, $record, $this->localeMode, true ),
                    );

                }

                $out['records'][] = array(
                    'id' => $record->getPublicId(),
                    'published' => true,
                    'fields' => $fieldData
                );
            } else {
                $out['records'][] = array(
                    'id'=>$record->getPublicId(),
                    'published' => false,
                );
            }
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;


    }



}
