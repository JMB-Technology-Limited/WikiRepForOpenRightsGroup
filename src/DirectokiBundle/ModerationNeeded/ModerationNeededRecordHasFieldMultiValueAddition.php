<?php

namespace DirectokiBundle\ModerationNeeded;

use DirectokiBundle\Entity\BaseRecordHasFieldMultiValue;
use DirectokiBundle\Entity\Event;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ModerationNeededRecordHasFieldMultiValueAddition extends  BaseModerationNeeded {

    /** @var BaseRecordHasFieldMultiValue */
    protected $fieldValue;

    function __construct( BaseRecordHasFieldMultiValue  $fieldValue ) {
        $this->fieldValue = $fieldValue;
    }


    public function getFieldValue() {
        return $this->fieldValue;
    }

    public function getEvent() {
        return $this->fieldValue->getAdditionCreationEvent();
    }

    public function approve( Event $event ) {
        $this->fieldValue->setAdditionApprovalEvent( $event );
        $this->fieldValue->setAdditionApprovedAt(new \DateTime());
        return $this->fieldValue;
    }

    public function reject( Event $event ) {
        $this->fieldValue->setAdditionRefusalEvent( $event );
        $this->fieldValue->setAdditionRefusedAt(new \DateTime());
        return $this->fieldValue;
    }


    public function getActionLabel() {
        return "Add";
    }

}

