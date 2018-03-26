<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Exception\DataValidationException;
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
class API1ProjectDirectoryEditController extends API1ProjectDirectoryController
{

    use API1TraitLocale;

    protected function build( string $projectId, string $directoryId, Request $request ) {
        parent::build( $projectId, $directoryId , $request);

        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }

        $this->buildLocale($request);

    }


    public function newRecordData(string $projectId, string $directoryId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build($projectId, $directoryId, $request);
        //data

        if (!$this->project->isAPIModeratedEditAllowed()) {
            throw new AccessDeniedHttpException('Project Access Denied');
        }

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
                $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processAPI1Record($field, null, $parameterBag, $event, $this->localeMode));
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
                    $this->get('logger')->error('A new record on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);


            $record = new Record();
            $record->setDirectory($this->directory);
            $record->setCreationEvent($event);
            $record->setCachedState(RecordHasState::STATE_DRAFT);
            $doctrine->persist($record);

            // Also record a request to publish this record but don't approve it - moderator will do that.
            $recordHasState = new RecordHasState();
            $recordHasState->setRecord($record);
            $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
            $recordHasState->setCreationEvent($event);
            $doctrine->persist($recordHasState);

            foreach($fieldDataToSave as $entityToSave) {
                $entityToSave->setRecord($record);
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            $updateRecordCacheAction = new UpdateRecordCache($this->container);
            $updateRecordCacheAction->go($record);

            return array(
                'code'=>200,
                'response'=>array('success'=>true,'id'=>$record->getPublicId()),
            );

        } else {

            return array(
                'code'=>200,
                'response'=>array('success'=>true,),
            );

        }

    }

    public function newRecordJSONAction(string $projectId, string $directoryId, Request $request) {
        $data = $this->newRecordData($projectId, $directoryId, $request->request, $request);
        $response = new Response(json_encode($data['response']));
        $response->setStatusCode($data['code']);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function newRecordJSONPAction(string $projectId, string $directoryId, Request $request) {
        $data = $this->newRecordData($projectId, $directoryId, $request->query, $request);
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

}
