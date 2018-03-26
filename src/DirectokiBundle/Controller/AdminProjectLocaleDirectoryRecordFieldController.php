<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\DataHasStringField;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Form\Type\DataHasStringFieldType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryRecordFieldController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Directory */
    protected $directory;

    /** @var Record */
    protected $record;

    protected $field;

    protected function build(string $projectId, string $localeId, string $directoryId, string $recordId, string $fieldId) {
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
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Field');
        $this->field = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldId));
        if (!$this->field) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    



}
