<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    protected function build(string $projectId, string $localeId) {
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
    }

    public function indexAction(string $projectId, string $localeId)
    {

        // build
        $this->build($projectId, $localeId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');
        $directories = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:ProjectLocale:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directories' => $directories,
        ));

    }

    public function mapAction(string $projectId, string $localeId)
    {

        // build
        $this->build($projectId, $localeId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repoFields = $doctrine->getRepository('DirectokiBundle:Field');

        $fields = $repoFields->findByProjectAndType($this->project, FieldTypeLatLng::FIELD_TYPE_INTERNAL);

        if ($fields) {

            return $this->render('DirectokiBundle:ProjectLocale:map.html.twig', array(
                'project' => $this->project,
                'locale' => $this->locale,
                'fields' => $fields,
            ));

        } else {

            return $this->render('DirectokiBundle:ProjectLocale:map.nofields.html.twig', array(
                'project' => $this->project,
                'locale' => $this->locale,
            ));

        }

    }


    public function mapDataAction(string $projectId, string $localeId, Request $request)
    {

        // build
        $this->build($projectId, $localeId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repoFields = $doctrine->getRepository('DirectokiBundle:Field');
        $repoRecords = $doctrine->getRepository('DirectokiBundle:Record');

        $fields = $repoFields->findByProjectAndType($this->project, FieldTypeLatLng::FIELD_TYPE_INTERNAL);

        $data = array('data' => array());

        $selectedData = explode(',', $request->query->get('fields'));
        foreach($fields as $field) {
            $key = 'directory-'.$field->getDirectory()->getPublicId().'-field-'.$field->getPublicId();
            if (in_array($key, $selectedData)) {

                $data['data'][$key] = array('records'=>array());

                $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

                foreach($repoRecords->findBy(array('directory'=>$field->getDirectory(), 'cachedState'=>RecordHasState::STATE_PUBLISHED)) as $record) {

                    $values = $fieldType->getLatestFieldValuesFromCache($field, $record);
                    if (count($values) == 1 && $values[0]) {

                        $data['data'][$key]['records'][] = array(
                            'lat' => $values[0]->getLat(),
                            'lng' => $values[0]->getLng(),
                            'url' => $this->generateUrl('directoki_project_locale_directory_record_show', array(
                                'projectId'=>$this->project->getPublicId(),
                                'localeId'=>$this->locale->getPublicId(),
                                'directoryId'=>$field->getDirectory()->getPublicId(),
                                'recordId'=>$record->getPublicId(),
                                )),
                        );
                    }

                }

            }
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
