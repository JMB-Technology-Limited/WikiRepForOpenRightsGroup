<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasFieldDateValueRepository")
 * @ORM\Table(name="directoki_record_has_field_date_value")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasFieldDateValue extends BaseRecordHasFieldValue
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="value", type="date", nullable=true)
     */
    protected  $value;



    /**
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \DateTime $value nullable!
     */
    public function setValue( $value)
    {
        $this->value = $value;
    }




}

