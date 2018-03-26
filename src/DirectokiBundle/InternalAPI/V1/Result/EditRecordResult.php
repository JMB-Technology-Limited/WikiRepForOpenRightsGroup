<?php

namespace DirectokiBundle\InternalAPI\V1\Result;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EditRecordResult
{

    protected $success;

    protected $approved;

    protected $fieldErrors;


    function __construct(
        $success = false,
        $approved = false,
        $fieldErrors = array()
    ) {
        $this->success = $success;
        $this->approved = $approved;
        $this->fieldErrors = $fieldErrors;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return boolean
     */
    public function isApproved()
    {
        return $this->approved;
    }

    public function hasFieldErrors():bool {
        return (boolean)$this->fieldErrors;
    }

    public function hasFieldErrorsForField(string $publicId):bool {
        return isset($this->fieldErrors[$publicId]) && count($this->fieldErrors[$publicId]);
    }

    public function getFieldErrorsForField(string $publicId):array {
        return $this->fieldErrors[$publicId];
    }



}
