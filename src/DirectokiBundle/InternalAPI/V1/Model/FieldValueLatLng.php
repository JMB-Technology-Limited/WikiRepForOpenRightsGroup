<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldValueLatLng extends BaseFieldValue {

    protected $lat;
    protected $lng;

    function __construct( $publicID, $title, $lat, $lng ) {
        $this->publicID = $publicID;
        $this->title = $title;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

}
