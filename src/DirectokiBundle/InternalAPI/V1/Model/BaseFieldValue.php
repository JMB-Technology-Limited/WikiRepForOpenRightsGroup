<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseFieldValue {

    protected $publicID;

    protected $title;

    /**
     * @return mixed
     */
    public function getPublicID() {
        return $this->publicID;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }



}