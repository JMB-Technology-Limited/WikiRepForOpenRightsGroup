<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasFieldBooleanValueRepository")
 * @ORM\Table(name="directoki_record_has_field_boolean_value")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasFieldBooleanValue extends BaseRecordHasFieldValue
{

    /**
     * @var boolean
     *
     * @ORM\Column(name="value", type="boolean", nullable=false)
     */
    protected  $value;



    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(bool $value)
    {
        $this->value = $value;
    }




}

