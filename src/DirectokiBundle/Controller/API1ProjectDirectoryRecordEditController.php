<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\FieldType\StringFieldType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditController extends API1ProjectDirectoryRecordController
{

    use API1TraitLocale;

    protected function build(string $projectId, string $directoryId, string $recordId, Request $request) {
        parent::build($projectId, $directoryId, $recordId, $request);

        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }

        $this->buildLocale($request);

    }


    protected function editData(string $projectId, string $directoryId, string $recordId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $recordId , $request );

        if (!$this->project->isAPIModeratedEditAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }

        //data
        $doctrine = $this->getDoctrine()->getManager();


        $event = $this->get('directoki_event_builder_service')->build(
            $this->project,
            $this->getUser(),
            $request,
            $parameterBag->get('comment')
        );

        $fields = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

        $fieldDataToSave = array();
        $dataValidationErrors = array();
        foreach ( $fields as $field ) {

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            try {
                $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processAPI1Record($field, $this->record, $parameterBag, $event, $this->localeMode));
            } catch (DataValidationException $dataValidationError) {
                $dataValidationErrors[$field->getPublicId()] = $dataValidationError;
            }
        }

        if ($dataValidationErrors) {

            $out =  array(
                'code'=>400,
                'response'=>array(
                    'success'=>false,
                    'field_errors'=>[],
                ),
            );
            foreach($dataValidationErrors as $k=>$v) {
                $out['response']['field_errors'][$k] = [ $v->getMessage() ];
            }
            return $out;

        } else if ($fieldDataToSave) {

            $event->setAPIVersion(1);
            $email = trim($parameterBag->get('email'));
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->get('logger')->error('An edit on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            foreach($fieldDataToSave as $entityToSave) {
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            $updateRecordCacheAction = new UpdateRecordCache($this->container);
            $updateRecordCacheAction->go($this->record);

            return array(
                'code'=>200,
                'response'=>array('success'=>true,),
            );

        } else {
            return array(
                'code'=>200,
                'response'=>array('success'=>true,),
            );
        }

    }


    public function editJSONAction(string $projectId, string $directoryId, string $recordId, Request $request)
    {
        $data = $this->editData($projectId, $directoryId, $recordId, $request->request, $request);
        $response = new Response(json_encode($data['response']));
        $response->setStatusCode($data['code']);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function editJSONPAction(string $projectId, string $directoryId, string $recordId, Request $request)
    {
        $data = $this->editData($projectId, $directoryId, $recordId, $request->query, $request);
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $errorAs200 = $request->get('errorAs200') ? boolval($request->get('errorAs200')) : false;
        if ($errorAs200) {
            $data['code'] = 200;
        }
        $response = new Response($callback."(".json_encode($data['response']).");");
        $response->setStatusCode($data['code']);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }



    protected function newReportData(string $projectId, string $directoryId, string $recordId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $recordId, $request );


        if (!$this->project->isAPIReportAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }

        //data
        $doctrine = $this->getDoctrine()->getManager();

        if ($parameterBag->get('description')) {

            $event = $this->get('directoki_event_builder_service')->build(
                $this->project,
                $this->getUser(),
                $request,
                null
            );
            $event->setAPIVersion(1);
            $email = trim($parameterBag->get('email'));
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->get('logger')->error('A new report on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            $recordReport = new RecordReport();
            $recordReport->setCreationEvent($event);
            $recordReport->setRecord($this->record);
            $recordReport->setDescription($parameterBag->get('description'));
            $doctrine->persist($recordReport);

            $doctrine->flush();

            $updateRecordCacheAction = new UpdateRecordCache($this->container);
            $updateRecordCacheAction->go($this->record);

            return array(
                'success'=>true,
            );
        } else {
            return array(
                'success'=>false,
            );
        }

    }

    // TODO newReportJSONAction

    public function newReportJSONPAction(string $projectId, string $directoryId, string $recordId, Request $request) {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $errorAs200 = $request->get('errorAs200') ? boolval($request->get('errorAs200')) : false;
        if ($errorAs200) {
            $data['code'] = 200;
        }
        $response = new Response($callback."(".json_encode($this->newReportData($projectId, $directoryId, $recordId, $request->query, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;

    }



}
