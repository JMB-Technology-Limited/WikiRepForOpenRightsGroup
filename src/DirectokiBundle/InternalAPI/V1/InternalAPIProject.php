<?php

namespace DirectokiBundle\InternalAPI\V1;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\InternalAPI\V1\Model\Locale;
use DirectokiBundle\LocaleMode\BaseLocaleMode;
use DirectokiBundle\LocaleMode\NoLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIProject
{

    protected $container;

    /** @var  Project */
    protected $project;

    /** @var  BaseLocaleMode */
    protected $localeMode;

    function __construct($container, Project $project)
    {
        $this->container = $container;
        $this->project = $project;
        $this->localeMode = new NoLocaleMode();
    }

    /**
     * @param $directoryID
     * @return InternalAPIDirectory
     * @throws \Exception
     */
    function getDirectoryAPI( string $directoryID ) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryID));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }

        return new InternalAPIDirectory($this->container, $this->project, $directory, $this->localeMode);
    }

    function getLocaleByPublicId(string $publicId) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $locale = $doctrine->getRepository('DirectokiBundle:Locale')->findOneBy(array('project'=>$this->project, 'publicId'=>$publicId));
        if (!$locale) {
            throw new \Exception("Not Found Locale");
        }

        return new Locale($locale->getPublicId(), $locale->getTitle());
    }

    function setSingleLocaleModeByPublicId(string $publicId) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $locale = $doctrine->getRepository('DirectokiBundle:Locale')->findOneBy(array('project'=>$this->project, 'publicId'=>$publicId));
        if (!$locale) {
            throw new \Exception("Not Found Locale");
        }

        $this->localeMode = new SingleLocaleMode($locale);

        return $this;
    }

}
