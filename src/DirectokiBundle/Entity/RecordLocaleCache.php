<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity()
 * @ORM\Table(name="directoki_record_locale_cache")
 * @ORM\HasLifecycleCallbacks
 */
class RecordLocaleCache
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Record")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id", nullable=false)
     */
    protected $record;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", nullable=false)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="full_text_search", type="text", nullable=false)
     */
    protected $fullTextSearch;

    /**
     * @return mixed
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param mixed $record
     */
    public function setRecord($record)
    {
        $this->record = $record;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getFullTextSearch()
    {
        return $this->fullTextSearch;
    }

    /**
     * @param string $fullTextSearch
     */
    public function setFullTextSearch($fullTextSearch)
    {
        $this->fullTextSearch = $fullTextSearch;
    }




}

