<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseRecordHasFieldWithLocaleValue extends BaseRecordHasFieldValue
{



    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", nullable=false)
     */
    protected $locale;

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






}

