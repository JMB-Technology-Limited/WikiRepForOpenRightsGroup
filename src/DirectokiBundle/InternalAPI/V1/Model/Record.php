<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class Record {

    protected $projectPublicId;

    protected $directoryPublicId;

    protected $publicID;

    protected $fields;

    function __construct( $projectPublicId, $directoryPublicId, $publicID, $fields = array() ) {
        $this->projectPublicId = $projectPublicId;
        $this->directoryPublicId = $directoryPublicId;
        $this->publicID = $publicID;
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getProjectPublicId() {
        return $this->projectPublicId;
    }

    /**
     * @return mixed
     */
    public function getDirectoryPublicId() {
        return $this->directoryPublicId;
    }

    /**
     * @return mixed
     */
    public function getPublicID() {
        return $this->publicID;
    }

    public function getFieldValue($pubicId) {
        return isset($this->fields[$pubicId]) ? $this->fields[$pubicId] : null;
    }

}
