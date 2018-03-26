<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleDirectoryRecordController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Directory */
    protected $directory;

    /** @var Record */
    protected $record;

    protected function build(string $projectId, string $localeId, string $directoryId, string $recordId) {
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
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Record');
        $this->record = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$recordId));
        if (!$this->record || $this->record->getCachedState() != RecordHasState::STATE_PUBLISHED) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction(string $projectId, string $localeId, string $directoryId, string $recordId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);

        $fieldValues = array();
        foreach($fields as $field) {

            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

            // TODO this should pass $this->locale !!!
            $tmp = $fieldType->getLatestFieldValues($field, $this->record);
            $fieldValues[$field->getPublicId()] = $fieldType->isMultipleType() ? $tmp : (count($tmp) > 0 ? $tmp[0] : null);

        }


        return $this->render('DirectokiBundle:ProjectLocaleDirectoryRecord:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'fields' => $fields,
            'fieldValues' => $fieldValues,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
        ));

    }

}
