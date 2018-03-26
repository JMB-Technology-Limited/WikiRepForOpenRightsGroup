<?php

namespace DirectokiBundle\InternalAPI\V1\Result;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ReportRecordResult
{

    protected $success;

    function __construct(
        $success = false
    ) {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

}
