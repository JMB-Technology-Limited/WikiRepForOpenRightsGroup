<?php

namespace DirectokiBundle\Cron;

use DirectokiBundle\Entity\Field;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateFieldCache extends BaseCron
{

    protected $actionSelectValue;

    function __construct($container)
    {
        parent::__construct($container);
        $this->actionSelectValue = new \DirectokiBundle\Action\UpdateSelectValueCache($container);
    }

    function runForField(Field $field)
    {
        $doctrine = $this->container->get('doctrine')->getManager();
        foreach ($doctrine->getRepository('DirectokiBundle:SelectValue')->findByField($field) as $selectValue) {
            $this->actionSelectValue->go($selectValue);
        }
    }

}
