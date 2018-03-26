<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasFieldLatLngValueRepository")
 * @ORM\Table(name="directoki_record_has_field_lat_lng_value")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasFieldLatLngValue extends BaseRecordHasFieldValue
{

    /**
     * @var string
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     */
    protected  $lat;

    /**
     * @var string
     *
     * @ORM\Column(name="lng", type="float", nullable=true)
     */
    protected  $lng;

    /**
     * @return string
     */
    public function getLat() {
        return $this->lat;
    }

    /**
     * @param string $lat
     */
    public function setLat( float $lat  = null) {
        $this->lat = $lat;
    }

    /**
     * @return string
     */
    public function getLng() {
        return $this->lng;
    }

    /**
     * @param string $lng
     */
    public function setLng( float $lng  = null) {
        $this->lng = $lng;
    }

}

