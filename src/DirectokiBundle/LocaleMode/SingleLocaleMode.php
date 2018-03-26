<?php

namespace DirectokiBundle\LocaleMode;
use DirectokiBundle\Entity\Locale;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class  SingleLocaleMode extends  BaseLocaleMode
{

    /** @var  Locale */
    protected $locale;

    /**
     * SingleLocaleMode constructor.
     * @param $locale
     */
    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

}
