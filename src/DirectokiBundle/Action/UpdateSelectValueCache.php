<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Entity\RecordLocaleCache;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateSelectValueCache
{


    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function go(SelectValue $selectValue)
    {

        $doctrine = $this->container->get('doctrine')->getManager();

        $locales = $doctrine->getRepository('DirectokiBundle:Locale')->findByProject($selectValue->getField()->getDirectory()->getProject());

        $titles = [];

        $selectValueHasTitleRepo = $doctrine->getRepository('DirectokiBundle:SelectValueHasTitle');

        foreach($locales as $locale) {
            $lastTitle = $selectValueHasTitleRepo->findOneBy(
                ['selectValue'=>$selectValue, 'locale'=>$locale],
                ['createdAt'=>'desc']
            );
            if ($lastTitle) {
                $titles[$locale->getId()] = $lastTitle->getTitle();
            }
        }

        $selectValue->setCachedTitles($titles);

        $doctrine->persist($selectValue);
        $doctrine->flush($selectValue);

    }

}
