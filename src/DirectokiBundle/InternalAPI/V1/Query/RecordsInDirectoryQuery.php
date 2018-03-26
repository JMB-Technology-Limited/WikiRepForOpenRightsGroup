<?php

namespace DirectokiBundle\InternalAPI\V1\Query;

use DirectokiBundle\InternalAPI\V1\Model\Locale;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordsInDirectoryQuery {



    /** @var  Locale */
    protected $locale;

    protected $fullTextSearch = null;

    function __construct(Locale $locale = null)
    {
        $this->locale = $locale;
    }


    /**
     * @return mixed
     */
    public function getFullTextSearch()
    {
        return $this->fullTextSearch;
    }

    /**
     * @param mixed $fullTextSearch
     */
    public function setFullTextSearch($fullTextSearch)
    {
        $this->fullTextSearch = $fullTextSearch;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

}


