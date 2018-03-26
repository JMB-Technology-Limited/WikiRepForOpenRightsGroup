<?php

namespace DirectokiBundle\ModerationNeeded;

use DirectokiBundle\Entity\BaseRecordHasFieldValue;
use DirectokiBundle\Entity\Event;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ModerationNeededRecordHasFieldValue extends  BaseModerationNeeded {

    /** @var BaseRecordHasFieldValue */
    protected $fieldValue;

    function __construct( BaseRecordHasFieldValue  $fieldValue ) {
        $this->fieldValue = $fieldValue;
    }


    public function getFieldValue() {
        return $this->fieldValue;
    }

    public function getEvent() {
        return $this->fieldValue->getCreationEvent();
    }

    public function approve( Event $event ) {
        $this->fieldValue->setApprovalEvent( $event );
        $this->fieldValue->setApprovedAt(new \DateTime());
        return $this->fieldValue;
    }

    public function reject( Event $event ) {
        $this->fieldValue->setRefusalEvent( $event );
        $this->fieldValue->setRefusedAt(new \DateTime());
        return $this->fieldValue;
    }


    public function getActionLabel() {
        return "Edit";
    }

}

