<?php

namespace DirectokiBundle\ModerationNeeded;

use DirectokiBundle\Entity\Event;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseModerationNeeded {

    public abstract function getFieldValue();

    public abstract function getEvent();

    public abstract function approve(Event $event);

    public abstract function reject(Event $event);

    public abstract function getActionLabel();

}
