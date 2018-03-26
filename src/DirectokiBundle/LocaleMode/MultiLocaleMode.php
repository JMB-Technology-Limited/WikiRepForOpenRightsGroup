<?php

namespace DirectokiBundle\LocaleMode;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class  MultiLocaleMode extends  BaseLocaleMode
{


    protected $locales;

    /**
     * MultiLocaleMode constructor.
     * @param $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @return mixed
     */
    public function getLocales()
    {
        return $this->locales;
    }

    public function containsPublicId($publicId) {
        foreach($this->locales as $locale) {
            if ($locale->getPublicId() == $publicId) {
                return true;
            }
        }
        return false;
    }

}
