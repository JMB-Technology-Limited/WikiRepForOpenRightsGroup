<?php

namespace DirectokiBundle\InternalAPI\V1\Model;

use DirectokiBundle\Entity\Field;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 * @license 3-clause BSD
 * @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordReportEdit
{

    protected $description;

    protected $email;


    protected $user;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = trim($description);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = trim($email);
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


}
