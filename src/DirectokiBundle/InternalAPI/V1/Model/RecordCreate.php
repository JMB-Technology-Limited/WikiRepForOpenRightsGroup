<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordCreate {

    protected $projectPublicId;

    protected $directoryPublicId;

    protected $fieldsEdit = array();

    protected $approveInstantlyIfAllowed = true;

    protected $comment;

    protected $email;

    protected $user;


    public function __construct($projectPublicId, $directoryPublicId, $fieldsEdit) {
        $this->projectPublicId = $projectPublicId;
        $this->directoryPublicId = $directoryPublicId;
        $this->fieldsEdit = $fieldsEdit;

    }


    public function getFieldValueEdit($pubicId) {
        return isset($this->fieldsEdit[$pubicId]) ? $this->fieldsEdit[$pubicId] : null;
    }

    public function getFieldValueEdits() {
        return $this->fieldsEdit;
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
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment( $comment ) {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail( $email ) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user ) {
        $this->user = $user;
    }

    /**
     * @return boolean
     */
    public function isApproveInstantlyIfAllowed()
    {
        return $this->approveInstantlyIfAllowed;
    }

    /**
     * @param boolean $approveInstantlyIfAllowed
     */
    public function setApproveInstantlyIfAllowed($approveInstantlyIfAllowed)
    {
        $this->approveInstantlyIfAllowed = $approveInstantlyIfAllowed;
    }


}

