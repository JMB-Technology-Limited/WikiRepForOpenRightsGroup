<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use DirectokiBundle\Entity\Field;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueLatLngEdit extends FieldValueLatLng {

    protected $newLat;
    protected $newLng;

    public function __construct(FieldValueLatLng $fieldValueLatLng = null, Field $field = null) {
        if ($fieldValueLatLng) {
            $this->publicID = $fieldValueLatLng->publicID;
            $this->title = $fieldValueLatLng->title;
            $this->lat = $fieldValueLatLng->lat;
            $this->lng = $fieldValueLatLng->lng;
            $this->newLat = $fieldValueLatLng->lat;
            $this->newLng = $fieldValueLatLng->lng;
        } else {
            $this->publicID = $field->getPublicId();
            $this->title = $field->getTitle();
        }
    }

    /**
     * @return mixed
     */
    public function getNewLat()
    {
        return $this->newLat;
    }

    /**
     * @param mixed $newLat
     */
    public function setNewLat($newLat)
    {
        $this->newLat = $newLat;
    }

    /**
     * @return mixed
     */
    public function getNewLng()
    {
        return $this->newLng;
    }

    /**
     * @param mixed $newLng
     */
    public function setNewLng($newLng)
    {
        $this->newLng = $newLng;
    }





}
