<?php

namespace DirectokiBundle\Cron;

use DirectokiBundle\Entity\Record;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DeleteOldInformation extends BaseCron
{

    protected $action;

    function __construct($container)
    {
        parent::__construct($container);
        $this->action = new \DirectokiBundle\Action\DeleteOldInformation($container);
    }

    function run()
    {
        $this->action->go();
    }


}
