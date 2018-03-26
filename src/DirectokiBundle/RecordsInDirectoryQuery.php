<?php

namespace DirectokiBundle;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Locale;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordsInDirectoryQuery {


    /** @var Directory  */
    protected $directory;

    /** @var  Locale */
    protected $locale;

    protected $fullTextSearch = null;

    protected $publishedOnly = false;

    function __construct(Directory $directory, Locale $locale = null)
    {
        $this->directory = $directory;
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
     * @return boolean
     */
    public function isPublishedOnly()
    {
        return $this->publishedOnly;
    }

    /**
     * @param boolean $publishedOnly
     */
    public function setPublishedOnly($publishedOnly)
    {
        $this->publishedOnly = $publishedOnly;
    }

    /**
     * @return Directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param Directory $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
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


