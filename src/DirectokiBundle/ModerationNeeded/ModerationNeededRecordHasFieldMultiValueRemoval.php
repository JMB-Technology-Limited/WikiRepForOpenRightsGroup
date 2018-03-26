<?php

namespace DirectokiBundle\ModerationNeeded;

use DirectokiBundle\Entity\BaseRecordHasFieldMultiValue;
use DirectokiBundle\Entity\Event;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ModerationNeededRecordHasFieldMultiValueRemoval extends  BaseModerationNeeded {

    /** @var BaseRecordHasFieldMultiValue */
    protected $fieldValue;

    function __construct( BaseRecordHasFieldMultiValue  $fieldValue ) {
        $this->fieldValue = $fieldValue;
    }


    public function getFieldValue() {
        return $this->fieldValue;
    }

    public function getEvent() {
        return $this->fieldValue->getRemovalCreationEvent();
    }

    public function approve( Event $event ) {
        $this->fieldValue->setRemovalApprovalEvent( $event );
        $this->fieldValue->setRemovalApprovedAt(new \DateTime());
        return $this->fieldValue;
    }

    public function reject( Event $event ) {
        $this->fieldValue->setRemovalRefusalEvent( $event );
        $this->fieldValue->setRemovalRefusedAt(new \DateTime());
        return $this->fieldValue;
    }

    public function getActionLabel() {
        return "Remove";
    }

}

