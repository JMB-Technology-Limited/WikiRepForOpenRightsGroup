<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasFieldMultiSelectValueRepository")
 * @ORM\Table(name="directoki_record_has_field_multi_select_value")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasFieldMultiSelectValue extends BaseRecordHasFieldMultiValue
{

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\SelectValue")
     * @ORM\JoinColumn(name="select_value_id", referencedColumnName="id", nullable=false)
     */
    private $selectValue;

    /**
     * @return mixed
     */
    public function getSelectValue() {
        return $this->selectValue;
    }

    /**
     * @param mixed $selectValue
     */
    public function setSelectValue( $selectValue ) {
        $this->selectValue = $selectValue;
    }

}

