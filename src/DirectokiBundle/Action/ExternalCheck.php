<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\DirectokiBundle;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordReport;
use GuzzleHttp\Exception\RequestException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ExternalCheck {


    protected $container;

    protected $guzzle;

    protected $failedChecksAllowed;

    public function __construct($container)
    {
        $this->container = $container;
        $this->guzzle = new \GuzzleHttp\Client();
        // TODO no magic numbers, take $failedChecksAllowed from configuration
        $this->failedChecksAllowed = 5;
    }


    public function go(Record $record) {

        if ($this->failedChecksAllowed < 1) {
            return;
        }

        $doctrine = $this->container->get('doctrine')->getManager();

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($record->getDirectory());
        foreach($fields as $field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            foreach($fieldType->getURLsForExternalCheck($field, $record) as $url) {
                $this->processURL($url, $record);
            }
        }

    }

    protected function processURL(string $url, Record $record) {

        if ($this->wasURLCheckedRecently($url, $record->getDirectory()->getProject())) {
            return;
        }

        $res = null;
        try {
            $res = $this->guzzle->request('GET', $url, ['timeout' => 10, 'allow_redirects' => true, 'http_errors' => false]);
            $this->recordResult($url, $record, $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\TooManyRedirectsException $e) {
            $this->recordResult($url, $record, null, "This website tried to make to many redirects.");
        } catch (RequestException $e) {
            $this->recordResult($url, $record, $res ? $res->getStatusCode() : null, get_class($e). ' '. $e->getMessage());
        }

    }


    protected function wasURLCheckedRecently(string $url, Project $project) {

        $doctrine = $this->container->get('doctrine')->getManager();

        return $doctrine->getRepository('DirectokiBundle:ExternalCheck')->wasURLCheckedRecently($url, $project);

    }

    protected function recordResult(string $url, Record $record, $httpResponseCode, $errorMessage = '') {

        $doctrine = $this->container->get('doctrine')->getManager();

        $externalCheck = new \DirectokiBundle\Entity\ExternalCheck();
        $externalCheck->setProject($record->getDirectory()->getProject());
        $externalCheck->setURL($url);
        $externalCheck->setHttpResponseCode($httpResponseCode);
        $externalCheck->setErrorMessage($errorMessage);

        if ($errorMessage || $httpResponseCode == 404 || $httpResponseCode == 410 || $httpResponseCode >= 500) {

            $this->failedChecksAllowed--;

            $event = $this->container->get('directoki_event_builder_service')->build(
                $record->getDirectory()->getProject(),
                null,
                null,
                null
            );
            $doctrine->persist($event);

            $recordReport = new RecordReport();
            $recordReport->setRecord($record);
            $recordReport->setExternalCheck($externalCheck);
            $recordReport->setCreationEvent($event);
            $recordReport->setDescription($url. " does not return a web page.\n\nResponse ". $httpResponseCode."\n\n".$errorMessage);

            $doctrine->persist($recordReport);

        }

        $doctrine->persist($externalCheck);
        $doctrine->flush();

    }

}
