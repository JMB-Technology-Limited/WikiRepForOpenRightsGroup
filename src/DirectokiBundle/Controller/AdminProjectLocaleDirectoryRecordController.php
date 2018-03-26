<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryRecordController extends Controller
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
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Locale');
        $this->locale = $repository->findOneBy(array('project' => $this->project, 'publicId' => $localeId));
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
        if (!$this->record) {
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

            $tmp = $fieldType->getLatestFieldValues($field, $this->record);
            $fieldValues[$field->getPublicId()] = $fieldType->isMultipleType() ? $tmp : (count($tmp) > 0 ? $tmp[0] : null);

        }

        $notes = $doctrine->getRepository('DirectokiBundle:RecordNote')->findByRecord($this->record, array('createdAt'=>'ASC'));
        $reports = $doctrine->getRepository('DirectokiBundle:RecordReport')->findByRecord($this->record, array('createdAt'=>'ASC'));

        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecord:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
            'fields' => $fields,
            'fieldValues' => $fieldValues,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
            'notes' => $notes,
            'reports' => $reports,
            'state' => $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record),
        ));

    }


    public function contactsAction(string $projectId, string $localeId, string $directoryId, string $recordId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();



        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryRecord:contacts.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'record' => $this->record,
        ));

    }




}
