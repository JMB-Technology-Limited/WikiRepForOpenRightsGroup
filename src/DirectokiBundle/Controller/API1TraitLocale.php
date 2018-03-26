<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\LocaleMode\MultiLocaleMode;
use DirectokiBundle\LocaleMode\NoLocaleMode;
use DirectokiBundle\LocaleMode\SingleLocaleMode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
trait  API1TraitLocale {


    /** @var BaseLocaleMode */
    protected $localeMode;

    protected function buildLocale(Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        $localeRepository = $doctrine->getRepository('DirectokiBundle:Locale');

        if (trim($request->get('locale'))) {
            $locale = $localeRepository->findOneBy(array('project'=>$this->project, 'publicId'=>trim($request->get('locale'))));
            if (!$locale) {
                throw new  NotFoundHttpException('Locale Not found');
            }
            $this->localeMode = new SingleLocaleMode($locale);
        } else if (trim($request->get('locales'))) {
            if (trim($request->get('locales')) == '*') {
                $this->localeMode = new MultiLocaleMode($localeRepository->findByProject($this->project));
            } else {
                $locales = array();
                foreach (explode(",", $request->get('locales')) as $publicId) {
                    if (trim($publicId)) {
                        $locale = $localeRepository->findOneBy(array('project' => $this->project, 'publicId' => trim($publicId)));
                        if ($locale) {
                            $locales[] = $locale;
                        } else {
                            throw new  NotFoundHttpException('Locale Not found: '.trim($publicId));
                        }
                    }
                }
                if ($locales) {
                    $this->localeMode = new MultiLocaleMode($locales);
                }
            }
        }

        if (!$this->localeMode) {
            $locale = $localeRepository->findOneByProject($this->project);
            if ($locale) {
                $this->localeMode = new SingleLocaleMode($locale);
            } else {
                $this->localeMode = new NoLocaleMode();
            }
        }
    }



}

