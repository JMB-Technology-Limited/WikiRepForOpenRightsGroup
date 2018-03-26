<?php

namespace DirectokiBundle\Cron;

use DirectokiBundle\Entity\Record;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateRecordCache extends BaseCron
{

    protected $action;

    function __construct($container)
    {
        parent::__construct($container);
        $this->action = new \DirectokiBundle\Action\UpdateRecordCache($container);
    }

    function runForRecord(Record $record)
    {
        $this->action->go($record);
    }


}
