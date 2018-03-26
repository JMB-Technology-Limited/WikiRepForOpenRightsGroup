<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseRecordHasFieldMultiValue
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Field")
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id", nullable=false)
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Record")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id", nullable=false)
     */
    protected $record;

    #########

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="addition_created_at", type="datetime", nullable=false)
     */
    protected $additionCreatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="addition_creation_event_id", referencedColumnName="id", nullable=false)
     */
    protected $additionCreationEvent;


    /**
     * @var \DateTime $approvedAt
     *
     * @ORM\Column(name="addition_approved_at", type="datetime", nullable=true)
     */
    protected $additionApprovedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="addition_approval_event_id", referencedColumnName="id", nullable=true)
     */
    protected $additionApprovalEvent;


    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="addition_refused_at", type="datetime", nullable=true)
     */
    protected $additionRefusedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="addition_refusal_event_id", referencedColumnName="id", nullable=true)
     */
    protected $additionRefusalEvent;

    ########

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="removal_created_at", type="datetime", nullable=true)
     */
    protected $removalCreatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="removal_creation_event_id", referencedColumnName="id", nullable=true)
     */
    protected $removalCreationEvent;


    /**
     * @var \DateTime $approvedAt
     *
     * @ORM\Column(name="removal_approved_at", type="datetime", nullable=true)
     */
    protected $removalApprovedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="removal_approval_event_id", referencedColumnName="id", nullable=true)
     */
    protected $removalApprovalEvent;


    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="removal_refused_at", type="datetime", nullable=true)
     */
    protected $removalRefusedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="removal_refusal_event_id", referencedColumnName="id", nullable=true)
     */
    protected $removalRefusalEvent;

    /**
     * @return mixed
     */
    public function getAdditionApprovalEvent() {
        return $this->additionApprovalEvent;
    }

    /**
     * @param Event $additionApprovalEvent
     */
    public function setAdditionApprovalEvent( Event $additionApprovalEvent = null ) {
        $this->additionApprovalEvent = $additionApprovalEvent;
        if ($additionApprovalEvent && !$this->additionApprovedAt) {
            $this->additionApprovedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getAdditionApprovedAt() {
        return $this->additionApprovedAt;
    }

    /**
     * @param \DateTime $additionApprovedAt
     */
    public function setAdditionApprovedAt( $additionApprovedAt ) {
        $this->additionApprovedAt = $additionApprovedAt;
    }

    /**
     * @return \DateTime
     */
    public function getAdditionCreatedAt() {
        return $this->additionCreatedAt;
    }

    /**
     * @param \DateTime $additionCreatedAt
     */
    public function setAdditionCreatedAt( $additionCreatedAt ) {
        $this->additionCreatedAt = $additionCreatedAt;
    }

    /**
     * @return mixed
     */
    public function getAdditionCreationEvent() {
        return $this->additionCreationEvent;
    }

    /**
     * @param Event $additionCreationEvent
     */
    public function setAdditionCreationEvent(Event $additionCreationEvent = null ) {
        $this->additionCreationEvent = $additionCreationEvent;
        if ($additionCreationEvent && !$this->additionCreatedAt) {
            $this->additionCreatedAt = new \DateTime();
        }
    }

    /**
     * @return mixed
     */
    public function getAdditionRefusalEvent() {
        return $this->additionRefusalEvent;
    }

    /**
     * @param Event $additionRefusalEvent
     */
    public function setAdditionRefusalEvent( Event $additionRefusalEvent = null ) {
        $this->additionRefusalEvent = $additionRefusalEvent;
        if ($additionRefusalEvent && !$this->additionRefusedAt) {
            $this->additionRefusedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getAdditionRefusedAt() {
        return $this->additionRefusedAt;
    }

    /**
     * @param \DateTime $additionRefusedAt
     */
    public function setAdditionRefusedAt( $additionRefusedAt ) {
        $this->additionRefusedAt = $additionRefusedAt;
    }

    /**
     * @return mixed
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField( $field ) {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId( int $id ) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getRecord() {
        return $this->record;
    }

    /**
     * @param mixed $record
     */
    public function setRecord( $record ) {
        $this->record = $record;
    }

    /**
     * @return mixed
     */
    public function getRemovalApprovalEvent() {
        return $this->removalApprovalEvent;
    }

    /**
     * @param Event $removalApprovalEvent
     */
    public function setRemovalApprovalEvent(Event $removalApprovalEvent = null ) {
        $this->removalApprovalEvent = $removalApprovalEvent;
        if ($removalApprovalEvent && !$this->removalApprovedAt) {
            $this->removalApprovedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getRemovalApprovedAt() {
        return $this->removalApprovedAt;
    }

    /**
     * @param \DateTime $removalApprovedAt
     */
    public function setRemovalApprovedAt( $removalApprovedAt ) {
        $this->removalApprovedAt = $removalApprovedAt;
    }

    /**
     * @return \DateTime
     */
    public function getRemovalCreatedAt() {
        return $this->removalCreatedAt;
    }

    /**
     * @param \DateTime $removalCreatedAt
     */
    public function setRemovalCreatedAt( $removalCreatedAt ) {
        $this->removalCreatedAt = $removalCreatedAt;
    }

    /**
     * @return mixed
     */
    public function getRemovalCreationEvent() {
        return $this->removalCreationEvent;
    }

    /**
     * @param Event $removalCreationEvent
     */
    public function setRemovalCreationEvent(Event $removalCreationEvent  = null) {
        $this->removalCreationEvent = $removalCreationEvent;
        if ($removalCreationEvent && !$this->removalCreatedAt) {
            $this->removalCreatedAt = new \DateTime();
        }
    }

    /**
     * @return mixed
     */
    public function getRemovalRefusalEvent() {
        return $this->removalRefusalEvent;
    }

    /**
     * @param Event $removalRefusalEvent
     */
    public function setRemovalRefusalEvent(Event $removalRefusalEvent = null ) {
        $this->removalRefusalEvent = $removalRefusalEvent;
        if ($removalRefusalEvent && !$this->removalRefusedAt) {
            $this->removalRefusedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getRemovalRefusedAt() {
        return $this->removalRefusedAt;
    }

    /**
     * @param \DateTime $removalRefusedAt
     */
    public function setRemovalRefusedAt( $removalRefusedAt ) {
        $this->removalRefusedAt = $removalRefusedAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->additionCreatedAt = new \DateTime("", new \DateTimeZone("UTC"));
    }





}

