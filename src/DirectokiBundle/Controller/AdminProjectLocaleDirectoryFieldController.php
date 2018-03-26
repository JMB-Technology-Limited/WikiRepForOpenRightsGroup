<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectLocaleDirectoryFieldController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Directory */
    protected $directory;

    /** @var Field */
    protected $field;

    protected function build(string $projectId, string $localeId, string $directoryId, string $fieldId) {
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
        $repository = $doctrine->getRepository('DirectokiBundle:Field');
        $this->field = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldId));
        if (!$this->field) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function selectValuesAction(string $projectId, string $localeId, string $directoryId, string $fieldId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId, $fieldId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        $selectValues = $repo->findByFieldSortForLocale($this->field, $this->locale);
        $locales = $doctrine->getRepository('DirectokiBundle:Locale')->findByProject($this->project, array('title'=>'ASC'));


        return $this->render('DirectokiBundle:AdminProjectLocaleDirectoryField:selectValues.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'field' => $this->field,
            'selectValues' => $selectValues,
            'locales' => $locales,
        ));

    }

}
